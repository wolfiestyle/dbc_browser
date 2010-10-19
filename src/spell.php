<?php
if (!isset($_GET['id'])) die("Missing parameter 'id'");
require('database.php');
require('spell_desc.php');

function print_dbc_for_entry($table, $entry)
{
    list ($res, $nr) = query_dbc($table, $entry);
    if ($nr != 0)
    {
        $entry = mysql_fetch_assoc($res);
        $index = 0;
        foreach ($entry as $key => $value)
        {
            if ($index > 1)
                echo ", ";
            if ($index > 0)
                echo "$key: $value";
            $index++;
        }
    }
}

$spell_id = (int)$_GET['id'];
list ($result, $num_rows) = query_dbc("Spell", $spell_id);
?>
<html>
<head>
<title>Spell <?php echo $spell_id; ?></title>
</head>
<body>
<p><a href=".">Index</a> / <a href="spell.php?id=<?php echo $spell_id; ?>">Spell</a></p>
<?php
require('form_findid.html');
echo "<script language=\"JavaScript\">document.formid.id.value=$spell_id;</script>\n";
if ($num_rows == 0)
    echo "Spell $spell_id not found.\n";
else
{
    $spell_info = mysql_fetch_assoc($result);
    echo "<h2>Spell $spell_id: $spell_info[SpellName]";
    if ($spell_info['Rank'] != "") echo " ($spell_info[Rank])";
    echo "</h2>\n";
    echo "<p>$spell_info[Description]</p>\n";
    echo "<p><i>$spell_info[ToolTip]</i></p>\n";
    echo "<table border=1>\n";
    foreach ($spell_info as $field => $value)
    {
        // skip text fields (already displayed)
        if ($field == "SpellName" || $field == "Rank" || $field == "Description" || $field == "ToolTip")
            continue;
        // strip numeric index in effect fields
        $name = substr($field, 0, -1);
        // index for effect/attribute fields
        $eff_idx = (int)substr($field, -1, 1);
        echo "<tr><td>$field</td><td>";
        // links for fields with spell ids
        if ($value != 0 && ($name == "EffectTriggerSpell" || $field == "casterAuraSpell" || $field == "targetAuraSpell" || $field == "excludeCasterAuraSpell" || $field == "excludeTargetAuraSpell"))
            echo "<a href=\"spell.php?id=$value\">$value</a>";
        // search links for category fields
        else if ($value != 0 && ($field == "Category" || $name == "SpellVisual" || $field == "SpellIconID" || $field == "StartRecoveryCategory" || $field == "SpellDifficultyId"))
            echo "<a href=\"search.php?$field=$value\">$value</a>";
        else
            echo $value;
        echo "</td><td>";
        // describe dispel field
        if ($value != 0 && $field == "Dispel")
            echo $spell_dispel[$value];
        // describe mechanic
        else if ($value != 0 && $field == "Mechanic")
            echo $spell_mechanic[$value];
        // describe Attributes field
        else if ($field == "Attributes")
            echo get_attribute_desc(0, $value);
        // describe AttributesEx fields
        else if ($field == "AttributesEx")
            echo get_attribute_desc(1, $value);
        // describe AttributesExN fields
        else if ($name == "AttributesEx")
            echo get_attribute_desc($eff_idx, $value);
        // describe stance fields
        else if ($value != 0 && ($field == "Stances" || $field == "StancesNot"))
            echo get_stance_desc($value);
        // describe aura state fields
        else if ($value != 0 && ($field == "CasterAuraState" || $field == "TargetAuraState" || $field == "CasterAuraStateNot" || $field == "TargetAuraStateNot"))
            echo $aura_state[$value];
        // describe casting time field
        else if ($field == "CastingTimeIndex")
            print_dbc_for_entry("SpellCastTimes", $value);
        // describe interrupt flags field
        else if ($field == "InterruptFlags")
            print get_spell_int_desc($value);
        // describe aura interrupt flags field
        else if ($field == "AuraInterruptFlags")
            print get_aura_int_desc($value);
        // describe channel interrupt flags field
        else if ($field == "ChannelInterruptFlags")
            print get_channel_int_desc($value);
        // describe proc field
        else if ($field == "procFlags")
            echo get_proc_desc($value);
        // describe duration field
        else if ($field == "DurationIndex")
            print_dbc_for_entry("SpellDuration", $value);
        // describe power type field
        else if ($field == "powerType")
            echo $power_type[$value];
        // describe range field
        else if ($field == "rangeIndex")
            print_dbc_for_entry("SpellRange", $value);
        // describe reagent/item fields
        else if ($value != 0 && ($name == "Reagent" || $name == "EffectItemType"))
        {
            list ($res, $nr) = query_world("item_template", $value);
            if ($nr)
            {
                $item_template = mysql_fetch_assoc($res);
                echo "<a href=\"http://www.wowhead.com/item=$value\">$item_template[name]</a>";
            }
        }
        // describe effect fields
        else if ($value !=0 && $name == "Effect")
        {
            echo $spell_effect[$value];
            if ($value == 28)
                $is_summon[$eff_idx] = true;
        }
        // describe target fields
        else if ($value != 0 && ($name == "EffectImplicitTargetA" || $name == "EffectImplicitTargetB"))
            echo $spell_target[$value];
        // describe radius fields
        else if ($value != 0 && $name == "EffectRadiusIndex")
            print_dbc_for_entry("SpellRadius", $value);
        // describe aura name fields
        else if ($value !=0 && $name == "EffectApplyAuraName")
        {
            echo $spell_aura[$value];
            switch ($value)
            {
                case 107:
                case 108:
                    $is_modifier[$eff_idx] = true;
                    break;
                case 14:
                case 71:
                case 123:
                case 163:
                case 179:
                case 186:
                case 199:
                case 269:
                case 87:
                case 183:
                case 229:
                case 310:
                    $misc_school_mask[$eff_idx] = true;
                    break;
                default:
                    break;
            }
        }
        // describe misc value fields
        else if ($name == "EffectMiscValue")
        {
            if ($is_modifier[$eff_idx])
                echo $spell_mod[$value];
            else if ($misc_school_mask[$eff_idx])
                echo get_school_desc($value);
            else if ($is_summon[$eff_idx])
            {
                list ($res, $nr) = query_world("creature_template", $value);
                if ($nr)
                {
                    $creature_template = mysql_fetch_assoc($res);
                    echo "<a href=\"http://www.wowhead.com/npc=$value\">$creature_template[name]</a>";
                }
            }
        }
        // describe misc value B fields
        else if ($name == "EffectMiscValueB")
        {
            if ($is_summon[$eff_idx])
                echo $summon_type[$value];  //TODO: use SummonProperties table
        }
        // describe trigger spell fields
        else if ($value != 0 && ($name == "EffectTriggerSpell" || $field == "casterAuraSpell" || $field == "targetAuraSpell" || $field == "excludeCasterAuraSpell" || $field == "excludeTargetAuraSpell"))
        {
            list ($res, $nr) = query_dbc("Spell", $value);
            if ($nr)
            {
                $sp_info = mysql_fetch_assoc($res);
                echo $sp_info['SpellName'];
                if ($sp_info['Rank'] != '')
                    echo "($sp_info[Rank])";
            }
        }
        // describe spell family field
        else if ($field == "SpellFamilyName")
            echo $spell_family[$value];
        // describe school field
        else if ($field == "SchoolMask")
            echo get_school_desc($value);
        // describe spell difficulty field
        else if ($field == "SpellDifficultyId")
            print_dbc_for_entry("SpellDifficulty", $value);
        echo "</td></tr>\n";
    }
    echo "</table>\n";
}
?>
</body>
</html>
