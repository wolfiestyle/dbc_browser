#include "reflection.h"

char const* const TypeToString[TYPE_MAX] =
{
    "(unknown)",        // TYPE_UNKNOWN
    "TINYINT",          // TYPE_INT8
    "TINYINT UNSIGNED", // TYPE_UINT8
    "SMALLINT",         // TYPE_INT16
    "SMALLINT UNSIGNED",// TYPE_UINT16
    "INT",              // TYPE_INT32
    "INT UNSIGNED",     // TYPE_UINT32
    "BIGINT",           // TYPE_INT64
    "BIGINT UNSIGNED",  // TYPE_UINT64
    "FLOAT",            // TYPE_FLOAT
    "DOUBLE",           // TYPE_DOUBLE
    "TEXT",             // TYPE_STRING
    "TEXT"              // TYPE_STRING_CONST
};

/*char const* const TypeToString[TYPE_MAX] =
{
    "(unknown)",        // TYPE_UNKNOWN
    "int8",             // TYPE_INT8
    "uint8",            // TYPE_UINT8
    "int16",            // TYPE_INT16
    "uint16",           // TYPE_UINT16
    "int32",            // TYPE_INT32
    "uint32",           // TYPE_UINT32
    "int64",            // TYPE_INT64
    "uint64",           // TYPE_UINT64
    "float",            // TYPE_FLOAT
    "double",           // TYPE_DOUBLE
    "char*",            // TYPE_STRING
    "char const*"       // TYPE_STRING_CONST
};*/
