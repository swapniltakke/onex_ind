<?php

class SidebarHelperMAI
{
    public static function hasExpandedAria($activePage = "", $needleParam = "")
    {
        if (str_contains($activePage, $needleParam)) {
            return "true";
        }
        return "false";
    }

    public static function getActiveClassDefinition($activePage = "", $needleParam = ""): string
    {
        if (str_contains($activePage, $needleParam)) {
            return "class='active'";
        }
        return "";
    }

    public static function getActiveClassDefinitionBylike($activePage = "", $needleParam = ""): string
    {
        if (str_contains($activePage, $needleParam)) {
            return "class='active'";
        }
        return "";
    }

    public static function getColapseClassTerm($activePage = "", $needleParam = "")
    {
        if (str_contains($activePage, $needleParam)) {
            return "in";
        }
        return "";
    }
}