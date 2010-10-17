#include <iostream>
#include <fstream>
#include "DBCStores.h"
#include "reflection.h"

static void strip_trailing(std::string& str, std::string const& remove)
{
    size_t str_len = str.size();
    size_t rem_len = remove.size();
    if (rem_len > str_len)
        return;
    if (str.substr(str_len - rem_len, rem_len) == remove)
        str.resize(str_len - rem_len);
}

static std::string escape_string(std::string const& str)
{
    std::string out;
    for (std::string::const_iterator i = str.begin(); i != str.end(); ++i)
    {
        if (*i == '\'' || *i == '"' || *i == '\\')
            out += '\\';
        out += *i;
    }
    return out;
}

template <typename T>
void dump_structure(std::ostream& out)
{
    std::string class_name = ClassInfo<T>::name;
    strip_trailing(class_name, "Entry");
    out << "DROP TABLE IF EXISTS " << class_name << ";\n";
    out << "CREATE TABLE " << class_name << " (\n";
    for (FieldList::const_iterator i = ClassInfo<T>::fields.begin(); i != ClassInfo<T>::fields.end(); ++i)
    {
        bool is_text = ((*i)->getType() == TYPE_STRING || (*i)->getType()== TYPE_STRING_CONST) &&
            (*i)->isArray() && (*i)->getArraySize() == 16;      // all string fields have 16 locales
        if (is_text && (*i)->getArrayIndex() != 0)              // only dump en_US locale
            continue;
        std::string field_name = (*i)->getName();
        if (is_text)
            strip_trailing(field_name, "0");
        out << '`' << field_name << "` " <<  (*i)->getTypeName() << " NOT NULL,\n";
    }
    out << "PRIMARY KEY (" << (*ClassInfo<T>::fields.begin())->getName() << ") );\n";
}

template <typename T>
void dump_data(std::ostream& out, DBCStorage<T> const& store, bool ext_insert = true)
{
    std::string class_name = ClassInfo<T>::name;
    strip_trailing(class_name, "Entry");
    if (ext_insert)
        out << "INSERT INTO " << class_name << " VALUES\n";
    uint32 last_row = store.GetNumRows() - 1;
    for (uint32 row = 0; row <= last_row; ++row)
    {
        T const* entry = store.LookupEntry(row);
        if (!entry)
            continue;
        if (ext_insert)
            out << "(";
        else
            out << "INSERT INTO " << class_name << " VALUES (";
        uint32 col = 0;
        for (FieldList::const_iterator itr = ClassInfo<T>::fields.begin(); itr != ClassInfo<T>::fields.end(); ++itr, ++col)
        {
            bool is_text = ((*itr)->getType() == TYPE_STRING || (*itr)->getType()== TYPE_STRING_CONST) &&
                (*itr)->isArray() && (*itr)->getArraySize() == 16;  // all string fields have 16 locales
            if (is_text && (*itr)->getArrayIndex() != 0)            // only dump en_US locale
                continue;
            if (col > 0)
                out << ", ";
            if (is_text)
                out << '\'' << escape_string((*itr)->getValue(const_cast<T*>(entry))) << '\'';
            else
                out << (*itr)->getValue(const_cast<T*>(entry));
        }
        if (ext_insert)
            out << (row < last_row ? "),\n" : ");\n");
        else
            out << ");\n";
    }
}

template <typename T>
void dump_all(DBCStorage<T> const& store, bool big_file = false)
{
    std::string file_name = ClassInfo<T>::name;
    strip_trailing(file_name, "Entry");
    file_name += ".sql";
    std::cerr << "Writing " << file_name << "...";
    std::ofstream fs(file_name.c_str());
    if (!fs.is_open())
    {
        std::cerr << "Error creating file " << file_name << std::endl;
        return;
    }
    dump_structure<T>(fs);
    dump_data<T>(fs, store, !big_file);
    fs.close();
    std::cerr << " done.\n";
}

int main(int argc, char **argv)
{
    LoadDBCStores("/opt/mangos_335/share/dbc");

    dump_all(sAchievementStore);
    dump_all(sAchievementCriteriaStore);
    dump_all(sGemPropertiesStore);
    dump_all(sItemStore, true);
    dump_all(sItemExtendedCostStore);
    dump_all(sSpellStore, true);
    dump_all(sSpellCastTimesStore);
    dump_all(sSpellDifficultyStore);
    dump_all(sSpellDurationStore);
    dump_all(sSpellItemEnchantmentStore);
    dump_all(sSpellRadiusStore);
    dump_all(sSpellRangeStore);
    dump_all(sSpellShapeshiftStore);
    dump_all(sSummonPropertiesStore);

    return 0;
}
