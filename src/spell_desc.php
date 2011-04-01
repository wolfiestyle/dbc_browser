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
    for ($i = 0; $i < 25; $i++)
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

function get_spell_int_desc($flags)
{
    global $spell_int_flag;
    $desc_string = '';
    for ($i = 0; $i < 6; $i++)
        if ($flags & (1 << $i))
        {
            if ($desc_string != '')
                $desc_string .= " | ";
            $desc_string .= $spell_int_flag[$i];
        }
    return $desc_string;
}

function get_aura_int_desc($flags)
{
    global $aura_int_flag;
    $desc_string = '';
    for ($i = 0; $i < 32; $i++)
        if ($flags & (1 << $i))
        {
            if ($desc_string != '')
                $desc_string .= " | ";
            $desc_string .= $aura_int_flag[$i];
        }
    return $desc_string;
}

function get_channel_int_desc($flags)
{
    global $chann_int_flag;
    $desc_string = '';
    for ($i = 0; $i < 6; $i++)
        if ($flags & (1 << $i))
        {
            if ($desc_string != '')
                $desc_string .= " | ";
            $desc_string .= $chann_int_flag[$i];
        }
    return $desc_string;
}

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

function calc_basepoints($spell_info, $eff_idx, $level, $combo_pts)
{
    if ($spell_info->maxLevel > 0 && $level > $spell_info->maxLevel)
        $level = $spell_info->maxLevel;
    else if ($level < $spell_info->baseLevel)
        $level = $spell_info->baseLevel;
    $level -= $spell_info->spellLevel;

    $bp_name = 'EffectBasePoints' . $eff_idx;
    $bp_per_level_name = 'EffectRealPointsPerLevel' . $eff_idx;
    $bp_rand_name = 'EffectDieSides' . $eff_idx;
    $bp_per_combo_name = 'EffectPointsPerComboPoint' . $eff_idx;

    $base_points = $spell_info->$bp_name + $level * $spell_info->$bp_per_level_name;
    $random_points = $spell_info->$bp_rand_name;
    $combo_dmg = $combo_pts * $spell_info->$bp_per_combo_name;

    $min_value = $base_points + $combo_dmg + 1;
    $max_value = $min_value;
    if ($random_points != 0 && $random_points != 1)
        $max_value += $random_points;

    return array($min_value, $max_value);
}
?>
