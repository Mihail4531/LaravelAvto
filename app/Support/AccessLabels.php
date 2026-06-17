<?php

namespace App\Support;

/**
 * Резолвер человекочитаемых имён для ролей и permissions.
 * Использует словари из lang/ru/roles.php и lang/ru/permissions.php.
 */
class AccessLabels
{
    public static function role(?string $name): string
    {
        if ($name === null || $name === '') {
            return '';
        }

        $translated = __("roles.{$name}");

        return is_string($translated) && $translated !== "roles.{$name}"
            ? $translated
            : $name;
    }

    public static function permission(?string $name): string
    {
        if ($name === null || $name === '') {
            return '';
        }

        $custom = __("permissions.custom.{$name}");
        if (is_string($custom) && $custom !== "permissions.custom.{$name}") {
            return $custom;
        }

        // Шаблон action_resource: "view_any_appointment" → "Просмотр списка заявок"
        foreach (['view_any', 'view', 'create', 'update', 'delete'] as $action) {
            $prefix = $action.'_';
            if (str_starts_with($name, $prefix)) {
                $resource = substr($name, strlen($prefix));
                $actionLabel = __("permissions.actions.{$action}");
                $resourceLabel = __("permissions.resources.{$resource}");

                if (
                    is_string($actionLabel)
                    && is_string($resourceLabel)
                    && $actionLabel !== "permissions.actions.{$action}"
                    && $resourceLabel !== "permissions.resources.{$resource}"
                ) {
                    return "{$actionLabel} {$resourceLabel}";
                }
            }
        }

        return $name;
    }
}
