<?php

namespace App\Helpers;


use Gate;

class HtmlBuilder
{
    /**
     * 生成菜单编辑器代码
     *
     * @param $menus
     * @return string
     */
    public static function menuEditor($menus)
    {
        $html = '<ol class="dd-list">';
        foreach ($menus as $menu) {
            $html .= '<li class="dd-item dd3-item" data-id="' . $menu->id . '">';
            $html .= '    <div class="dd-handle dd3-handle"></div>';
            $html .= '    <div class="dd3-content"><i class="fa ' . $menu->icon . '"></i> ' . $menu->name;
            $html .= '        <button class="btn btn-xs btn-danger pull-right btn-menu-remove" data-id="' . $menu->id . '"><i class="fa fa-times"></i></button>';
            $html .= '        <button class="btn btn-xs btn-success pull-right btn-menu-edit" data-id="' . $menu->id . '" data-name="' . $menu->name . '" data-icon="' . $menu->icon . '" data-url="' . $menu->url . '" data-permission="' . $menu->permission .'"><i class="fa fa-edit"></i></button>';
            $html .= '    </div>';

            if (count($menu->children) > 0) {
                $html .= static::menuEditor($menu->children()->orderBy('sort')->get());
            }

            $html .= '</li>';
        }
        $html .= '</ol>';

        return $html;
    }

    /**
     * 生成菜单树代码
     *
     * @param $menus
     * @return string
     */
    public static function menuTree($menus)
    {
        $html = '';
        foreach ($menus as $menu) {
            if (empty($menu->permission) || Gate::allows($menu->permission)) {
                if (count($menu->children)) {
                    $html .= '<li class="treeview">';
                } else {
                    $html .= '<li>';
                }
                $html .= '    <a href="' . $menu->url . '">';
                $html .= '        <i class="fa ' . $menu->icon . '"></i>';
                $html .= '        <span class="menu-item-top">' . $menu->name . '</span>';
                if (count($menu->children)) {
                    $html .= '        <i class="fa fa-angle-left pull-right"></i>';
                }
                $html .= '    </a>';
                if (count($menu->children)) {
                    $html .= '<ul class="treeview-menu">';
                    $html .= static::menuTree($menu->children()->orderBy('sort')->get());
                    $html .= '</ul>';
                }
                $html .= '</li>';
            }
        }
        return $html;
    }
}