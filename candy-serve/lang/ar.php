<?php

/**
 * Arabic translations for candy-serve.
 *
 * @return array<string, string>
 */

declare(strict_types=1);

return [
    'user.invalid_ssh_key'     => 'تنسيق مفتاح SSH العام غير صالح',
    'repo.create_dir_failed'   => 'فشل إنشاء دليل المستودع: {path}',
    'repo.git_init_failed'     => 'فشل git init: {output}',
    'config.not_found'         => 'ملف التكوين غير موجود: {path}',
    'config.read_failed'       => 'فشل قراءة التكوين: {path}',
    'ssh.user_cannot_create'   => 'المستخدم {viewer} لا يمكنه إنشاء مستودعات',
    'cli.banner'               => 'CandyServe v{version}',
    'cli.config_summary'       => "Data path:  {data_path}\nSSH:        {ssh_addr}\nHTTP:       {http_addr}\nGit daemon: {git_addr}",
    'cli.starting_servers'     => 'جاري بدء الخوادم...',
    'cli.note_ssh2_required'   => '(وضع守护进程 الكامل يتطلب امتداد ssh2 و daemon SSH قيد التشغيل.)',
    'cli.note_run_init'        => "(قم أولاً بتشغيل 'soft-serve init' لتهيئة دليل البيانات.)",
    'cli.repos_header'         => 'المستودعات:',
    'cli.repo_listing_entry'   => '  - {name}',
    'cli.repo_listing_plain'   => '  {name}',
    'cli.note_not_a_daemon'    => 'لم يتم التشغيل كـ守护进程 بعد (وضع daemon يتطلب مدير العمليات.)',
    'cli.note_http_help'       => 'لخدمة Git عبر HTTP، وجّه خادم الويب إلى هذا البرنامج النصي.',
    'cli.note_ssh_help'        => 'للوصول عبر SSH، استخدم نفقًا عكسيًا أو قم بتكوين sshd باستخدام ForceCommand.',
    'cli.already_initialized'  => 'مهيأ بالفعل: {path}',
    'cli.initializing'         => 'جاري تهيئة دليل بيانات CandyServe: {path}',
    'cli.done'                 => 'تم.',
    'cli.next_steps'           => 'الخطوات التالية:',
    'cli.next_step_1'          => '  1. قم بتحرير {path}/config.yaml',
    'cli.next_step_2'          => '  2. أنشئ مفتاح_host SSH: ssh-keygen -t ed25519 -f {path}/ssh/soft_serve_host',
    'cli.next_step_3'          => '  3. اضبط CANDY_SERVE_INITIAL_ADMIN_KEYS=مفتاح-ssh-العام',
    'cli.next_step_4'          => '  4. شغّل: soft-serve serve --config {path}/config.yaml',
    'cli.usage_user_root'      => 'الاستخدام: soft-serve user add|key|list',
    'cli.usage_user_add'       => 'الاستخدام: soft-serve user add <username>',
    'cli.usage_user_key'       => 'الاستخدام: soft-serve user key <username> [key-file]',
    'cli.user_created'         => "تم إنشاء المستخدم '{username}' (admin: true)",
    'cli.user_key_hint'        => "استخدم 'soft-serve user key {username} < key.pub' لإضافة مفتاح SSH العام.",
    'cli.user_key_read_failed' => 'فشل قراءة المفتاح من: {file}',
    'cli.user_key_added'       => "تمت إضافة المفتاح للمستخدم '{username}'",
    'cli.user_keys_header'     => 'المفاتيح المصرح بها:',
    'cli.user_key_entry'       => '  {prefix}...',
    'cli.user_list_empty'      => "المستخدمون:\n  (لا يوجد مستخدمون مسجلون. استخدم 'soft-serve user add <username>')",
    'cli.usage_repo_root'      => 'الاستخدام: soft-serve repo list|create|info',
    'cli.usage_repo_create'    => 'الاستخدام: soft-serve repo create <name>',
    'cli.usage_repo_info'      => 'الاستخدام: soft-serve repo info <name>',
    'cli.no_repos'             => 'لا توجد مستودعات حتى الآن.',
    'cli.repo_listing_none'    => '  (لا توجد مستودعات حتى الآن)',
    'cli.repo_invalid_name'    => 'اسم مستودع غير صالح: {name} (استخدم alphanumeric و punto و underscore و hyphen فقط)',
    'cli.repo_created'         => "تم إنشاء المستودع '{name}' في {path}",
    'cli.repo_clone_url'       => 'رابط الاستنساخ: ssh://localhost:23231/{name}',
    'cli.repo_not_found'       => 'المستودع غير موجود: {name}',
    'cli.repo_info'            => "الاسم:         {name}\nالوصف:  {description}\nعام:       {is_public}\nخاص:      {is_private}\nالسماح بالدفع:   {allow_push}\nالمسار:         {path}\nالفروع:     {branches}\nالعلامات:         {tags}",
    'cli.bool_yes'             => 'نعم',
    'cli.bool_no'              => 'لا',
    'cli.none_value'           => '(لا شيء)',
    'cli.unknown_command'      => "أمر غير معروف: {cmd}\n  شغّل 'soft-serve help' للمساعدة.",
    'cli.help'                 => "CandyServe — خادم Git ذاتي الاستضافة\n\nالاستخدام:\n  soft-serve <أمر> [خيارات]\n\nالأوامر:\n  serve [--config path]    بدء خادم Git\n  init [data-path]         تهيئة دليل بيانات جديد\n  user add <username>      إنشاء مستخدم\n  user key <username> <file>  إضافة مفتاح SSH عام للمستخدم (استخدم - لـ stdin)\n  user list                سرد المستخدمين\n  repo list [data-path]    سرد المستودعات\n  repo create <name> [data-path]  إنشاء مستودع\n  repo info <name> [data-path]    عرض معلومات المستودع\n  help, --help, -h         عرض هذه المساعدة\n  version, --version, -v   عرض الإصدار\n\nمتغيرات البيئة:\n  CANDY_SERVE_DATA_PATH    دليل البيانات (الافتراضي: /tmp/candy-serve)\n\nالأمثلة:\n  soft-serve init ~/candy-serve-data\n  soft-serve serve --config ~/candy-serve-data/config.yaml\n  soft-serve repo create my-project\n  soft-serve user key alice ~/.ssh/id_ed25519.pub",
];
