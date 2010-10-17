#!/usr/bin/perl
use strict;

my $desc = "Generates C++ source with strings of element names in structures.";
my $usage = "Usage: $0 <source_file>";
die "$desc\n\n$usage\n" if $#ARGV != 0;

my $source_file = $ARGV[0];
my %constants;

sub print_header
{
    print << "EOF";
#include "reflection.h"
#include "initializer.h"
#include "$source_file"

EOF
}

sub parse_include
{
    my ($filename) = @_;

    open(INCF, "<$filename");
    while (<INCF>)
    {
        # macro definition
        if ($_ =~ m/#define\s+(\w+)\s+(0x[0-9A-Za-z]+|\d+)/)
        {
            $constants{$1} = $2;
        }
    }
    close(INCF);
}

sub parse_file
{
    my ($filename) = @_;
    my $class_name = "";
    my $level = 0;

    &print_header();

    open(FP, "<$filename");
    while (<FP>)
    {
        # included file
        if ($_ =~ m/#include "([^"]+)"/)
        {
            &parse_include($1);
            next;
        }
        # macro definition
        if ($_ =~ m/#define\s+(\w+)\s+(0x[0-9A-Za-z]+|\d+)/)
        {
            $constants{$1} = $2;
            next;
        }
        # struct definition
        if ($_ =~ m/^(struct|class)\s+(\w+)/)
        {
            if ($level == 0)
            {
                $class_name = $2;
                print "template <>\nchar const* const ClassInfo<$class_name>::name = \"$class_name\";\n\n";
                print "template <>\nFieldList const ClassInfo<$class_name>::fields = list_initializer<FieldList>\n";
            }
        }
        # opening bracket
        if ($_ =~ m/\{/)
        {
            $level++;
        }
        # struct element
        if ($level == 1 && $class_name)
        {
            # single variable
            if ($_ =~ m/\s+(\w+(\sconst)?\*?)\s+(\w+);/)
            {
                #print "  type: '$1' field: '$2'\n";
                print "    (new FieldType<$1, $class_name>(&$class_name\::$3, \"$3\"))\n";
            }
            else
            {
                # array variable with numeric size
                if ($_ =~ m/\s+(\w+(\sconst)?\*?)\s+(\w+)\[(\d+)\];/)
                {
                    for (my $i = 0; $i < $4; $i++)
                    {
                        print "    (new FieldTypeArray<$1, $class_name, $4>(&$class_name\::$3, \"$3$i\", $i))\n";
                    }
                }
                else
                {
                    # array variable with macro/enum size
                    if ($_ =~ m/\s+(\w+(\sconst)?\*?)\s+(\w+)\[(\w+)\];/)
                    {
                        if (!$constants{$4})
                        {
                            die "$0: error: unknown identifier '$4'\n";
                        }
                        my $count = $constants{$4};
                        for (my $i = 0; $i < $count; $i++)
                        {
                            print "    (new FieldTypeArray<$1, $class_name, $count>(&$class_name\::$3, \"$3$i\", $i))\n";
                        }
                    }
                }
            }
        }
        # closing bracket
        if ($_ =~ m/\}/)
        {
            $level--;
            if ($level < 0)
            {
                die "$0: error: found '}' withouth matching '{'\n";
            }
            if ($level == 0)
            {
                if ($class_name)
                {
                    print "    ;\n\n";
                }
                $class_name = "";
            }
        }
    }
    close(FP);
}

&parse_file($source_file);
