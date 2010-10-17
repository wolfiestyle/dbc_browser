<?php
require('spell_defines.php');

function get_attribute_desc($ex, $flags)
{
    global $spell_attr;
    $desc_string = '';
    for ($i = 0; $i < 32; $i++)
        if ($flags & (1 << $i))
        {
            if ($desc_string != '')
                $desc_string .= " | ";
            $desc_string .= $spell_attr[$ex][$i];
        }
    return $desc_string;
}

function get_stance_desc($flags)
{
    global $shapeshift_form;
    $desc_string = '';
    for ($i = 0; $i < 32; $i++)
        if ($flags & (1 << $i))
        {
            if ($desc_string != '')
                $desc_string .= ", ";
            $desc_string .= $shapeshift_form[$i+1];
        }
    return $desc_string;
}

function get_proc_desc($flags)
{
    global $spell_proc;
    $desc_string = '';
    for ($i = 0; $i < 24; $i++)
        if ($flags & (1 << $i))
        {
            if ($desc_string != '')
                $desc_string .= " | ";
            $desc_string .= $spell_proc[$i];
        }
    return $desc_string;
}

function get_school_desc($flags)
{
    global $spell_school;
    $desc_string = '';
    for ($i = 0; $i < 7; $i++)
        if ($flags & (1 << $i))
        {
            if ($desc_string != '')
                $desc_string .= " | ";
            $desc_string .= $spell_school[$i];
        }
    return $desc_string;
}
?>
