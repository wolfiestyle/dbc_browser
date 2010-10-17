#ifndef __REFLECTION_H
#define __REFLECTION_H

#include <stdint.h>
#include <sstream>
#include <list>

enum TypeIdentifier
{
    TYPE_UNKNOWN,
    TYPE_INT8,
    TYPE_UINT8,
    TYPE_INT16,
    TYPE_UINT16,
    TYPE_INT32,
    TYPE_UINT32,
    TYPE_INT64,
    TYPE_UINT64,
    TYPE_FLOAT,
    TYPE_DOUBLE,
    TYPE_STRING,
    TYPE_STRING_CONST,
    // values count
    TYPE_MAX
};

template <typename T>
inline TypeIdentifier TypeToId() { return TYPE_UNKNOWN; }

template <> inline TypeIdentifier TypeToId<int8_t>()   { return TYPE_INT8; }
template <> inline TypeIdentifier TypeToId<uint8_t>()  { return TYPE_UINT8; }
template <> inline TypeIdentifier TypeToId<int16_t>()  { return TYPE_INT16; }
template <> inline TypeIdentifier TypeToId<uint16_t>() { return TYPE_UINT16; }
template <> inline TypeIdentifier TypeToId<int32_t>()  { return TYPE_INT32; }
template <> inline TypeIdentifier TypeToId<uint32_t>() { return TYPE_UINT32; }
template <> inline TypeIdentifier TypeToId<int64_t>()  { return TYPE_INT64; }
template <> inline TypeIdentifier TypeToId<uint64_t>() { return TYPE_UINT64; }
template <> inline TypeIdentifier TypeToId<float>()    { return TYPE_FLOAT; }
template <> inline TypeIdentifier TypeToId<double>()   { return TYPE_DOUBLE; }
template <> inline TypeIdentifier TypeToId<char*>()    { return TYPE_STRING; }
template <> inline TypeIdentifier TypeToId<char const*>() { return TYPE_STRING_CONST; }

extern char const* const TypeToString[TYPE_MAX];

class Field
{
public:
    virtual ~Field() {}

    TypeIdentifier getType() const { return m_type; }
    char const* getName() const { return m_name; }
    char const* getTypeName() const { return TypeToString[m_type]; }
    bool isArray() const { return getArraySize() != 0; }

    virtual std::string getValue(void* obj_ptr) const = 0;
    virtual size_t getArraySize() const { return 0; }
    virtual size_t getArrayIndex() const { return 0; }

protected:
    TypeIdentifier m_type;
    char const* const m_name;

    Field(TypeIdentifier _type, char const* _name):
        m_type(_type), m_name(_name)
    {
    }
};

template <typename T, typename Class>
class FieldType: public Field
{
public:
    typedef T Class::* MemberType;

    FieldType(MemberType const _member, char const* _name):
        Field(TypeToId<T>(), _name),
        m_member(_member)
    {
    }

    std::string getValue(void* obj_ptr) const
    {
        std::ostringstream ss;
        ss << reinterpret_cast<Class*>(obj_ptr)->*m_member;
        return ss.str();
    }

protected:
    MemberType const m_member;
};

template <typename T, typename Class, size_t N>
class FieldTypeArray: public Field
{
public:
    typedef T (Class::*MemberType)[N];

    FieldTypeArray(MemberType const _member, char const* _name, size_t _index):
        Field(TypeToId<T>(), _name),
        m_member(_member),
        m_index(_index)
    {
    }

    std::string getValue(void *obj_ptr) const
    {
        std::ostringstream ss;
        ss << (reinterpret_cast<Class*>(obj_ptr)->*m_member)[m_index];
        return ss.str();
    }

    size_t getArraySize() const { return N; }
    size_t getArrayIndex() const { return m_index; }

protected:
    MemberType const m_member;
    size_t const m_index;
};

typedef std::list<Field*> FieldList;

template <typename T>
struct ClassInfo
{
    static char const* const name;
    static FieldList const fields;
};

#endif // __REFLECTION_H
