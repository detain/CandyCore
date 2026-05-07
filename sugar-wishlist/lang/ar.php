<?php

/**
 * Arabic translations for sugar-wishlist.
 *
 * @return array<string, string>
 */

declare(strict_types=1);

return [
    'launcher.no_pcntl'        => 'pcntl_exec غير متاح; ext-pcntl مطلوب',
    'launcher.exec_failed'     => 'فشل تنفيذ {bin}',
    'config.not_found'         => 'تكوين wishlist غير موجود: {path}',
    'config.json_top_level'    => 'wishlist json: القيمة عالية المستوى يجب أن تكون مصفوفة',
    'config.json_entry_object' => 'wishlist json: كل إدخال يجب أن يكون كائنًا',
    'config.yaml_continuation' => "wishlist yaml: لا يمكن تعيين المتابعة قبل أي كتلة '- name:'",
    'config.yaml_unparseable'  => 'wishlist yaml: سطر غير قابل للتحليل: {line}',
    'config.entry_missing_field' => 'إدخال wishlist بحقل مطلوب مفقود: name أو host',
    'cli.usage'         => 'الاستخدام: wishlist [--config <مسار>] [--ssh <ssh-ثنائي>]',
    'cli.unknown_arg'   => 'wishlist: وسيطة غير معروفة: {arg}',
    'cli.no_config'     => 'wishlist: لم يتم العثور على تكوين. مرر --config <مسار>.',
    'cli.error'         => 'wishlist: {message}',
    'cli.cancelled'     => 'wishlist: تم الإلغاء.',
];
