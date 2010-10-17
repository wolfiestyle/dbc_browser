#ifndef __INITIALIZER_H
#define __INITIALIZER_H

/// initializer for map, multimap, unordered_map, unordered_multimap
template <typename ContainerType>
class map_initializer
{
protected:
    typedef typename ContainerType::key_type    KeyType;
    typedef typename ContainerType::mapped_type MappedType;
    typedef typename ContainerType::value_type  ValueType;

    ContainerType m_map;

public:
    map_initializer(KeyType const& key, MappedType const& val)
    {
        m_map.insert(ValueType(key, val));
    }

    map_initializer& operator() (KeyType const& key, MappedType const& val)
    {
        m_map.insert(ValueType(key, val));
        return *this;
    }

    operator ContainerType() const
    {
        return m_map;
    }
};

/// initializer for vector, deque, list
template <typename ContainerType>
class list_initializer
{
protected:
    typedef typename ContainerType::value_type ValueType;

    ContainerType m_list;

public:
    list_initializer(ValueType const& val)
    {
        m_list.push_back(val);
    }

    list_initializer& operator() (ValueType const& val)
    {
        m_list.push_back(val);
        return *this;
    }

    operator ContainerType() const
    {
        return m_list;
    }
};

/// initializer for set, multiset, unordered_set, unordered_multiset
template <typename ContainerType>
class set_initializer
{
protected:
    typedef typename ContainerType::value_type ValueType;

    ContainerType m_set;

public:
    set_initializer(ValueType const& val)
    {
        m_set.insert(val);
    }

    set_initializer& operator() (ValueType const& val)
    {
        m_set.insert(val);
        return *this;
    }

    operator ContainerType() const
    {
        return m_set;
    }
};

#endif // __INITIALIZER_H
