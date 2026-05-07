<?php

/**
 * Turkish translations for candy-serve.
 *
 * @return array<string, string>
 */

declare(strict_types=1);

return [
    'user.invalid_ssh_key'     => 'Geçersiz SSH açık anahtar biçimi',
    'repo.create_dir_failed'   => 'Depo dizini oluşturulamadı: {path}',
    'repo.git_init_failed'     => 'git init başarısız: {output}',
    'config.not_found'         => 'Yapılandırma dosyası bulunamadı: {path}',
    'config.read_failed'       => 'Yapılandırma okunamadı: {path}',
    'ssh.user_cannot_create'   => '{viewer} kullanıcısı depo oluşturamaz',
    'cli.banner'               => 'CandyServe v{version}',
    'cli.config_summary'       => "Data path:  {data_path}\nSSH:        {ssh_addr}\nHTTP:       {http_addr}\nGit daemon: {git_addr}",
    'cli.starting_servers'     => 'Sunucular başlatılıyor...',
    'cli.note_ssh2_required'   => '(Tam daemon modu ssh2 eklentisi ve çalışan SSH daemonu gerektirir.)',
    'cli.note_run_init'        => "('soft-serve init' çalıştırarak veri dizinini başlatın.)",
    'cli.repos_header'         => 'Depolar:',
    'cli.repo_listing_entry'   => '  - {name}',
    'cli.repo_listing_plain'   => '  {name}',
    'cli.note_not_a_daemon'    => 'Henüz daemon olarak çalışmıyor (daemon modu bir süreç yöneticisi gerektirir.)',
    'cli.note_http_help'       => 'Git\'i HTTP üzerinden sunmak için web sunucusunu bu betiğe yönlendirin.',
    'cli.note_ssh_help'        => 'SSH erişimi için ters tünel kullanın veya sshd\'yi ForceCommand ile yapılandırın.',
    'cli.already_initialized'  => 'Zaten başlatılmış: {path}',
    'cli.initializing'         => 'CandyServe veri dizini başlatılıyor: {path}',
    'cli.done'                 => 'Tamamlandı.',
    'cli.next_steps'           => 'Sonraki adımlar:',
    'cli.next_step_1'          => '  1. {path}/config.yaml dosyasını düzenleyin',
    'cli.next_step_2'          => '  2. SSH ana bilgisayar anahtarı oluşturun: ssh-keygen -t ed25519 -f {path}/ssh/soft_serve_host',
    'cli.next_step_3'          => '  3. CANDY_SERVE_INITIAL_ADMIN_KEYS=ssh-açık-anahtarı ayarlayın',
    'cli.next_step_4'          => '  4. Çalıştırın: soft-serve serve --config {path}/config.yaml',
    'cli.usage_user_root'      => 'Kullanım: soft-serve user add|key|list',
    'cli.usage_user_add'       => 'Kullanım: soft-serve user add <username>',
    'cli.usage_user_key'       => 'Kullanım: soft-serve user key <username> [key-file]',
    'cli.user_created'         => "'{username}' kullanıcısı oluşturuldu (admin: true)",
    'cli.user_key_hint'        => "SSH açık anahtarı eklemek için 'soft-serve user key {username} < key.pub' kullanın.",
    'cli.user_key_read_failed' => 'Anahtar okunamadı: {file}',
    'cli.user_key_added'       => "'{username}' kullanıcısına anahtar eklendi",
    'cli.user_keys_header'     => 'Yetkili anahtarlar:',
    'cli.user_key_entry'       => '  {prefix}...',
    'cli.user_list_empty'      => "Kullanıcılar:\n  (Kayıtlı kullanıcı yok. 'soft-serve user add <username>' kullanın)",
    'cli.usage_repo_root'      => 'Kullanım: soft-serve repo list|create|info',
    'cli.usage_repo_create'    => 'Kullanım: soft-serve repo create <name>',
    'cli.usage_repo_info'      => 'Kullanım: soft-serve repo info <name>',
    'cli.no_repos'             => 'Henüz depo yok.',
    'cli.repo_listing_none'    => '  (henüz depo yok)',
    'cli.repo_invalid_name'    => 'Geçersiz depo adı: {name} (yalnızca alfanümerik, nokta, alt çizgi, kısa çizgi kullanın)',
    'cli.repo_created'         => "'{name}' deposu oluşturuldu ({path})",
    'cli.repo_clone_url'       => 'Klon URL\'si: ssh://localhost:23231/{name}',
    'cli.repo_not_found'       => 'Depo bulunamadı: {name}',
    'cli.repo_info'            => "İsim:         {name}\nAçıklama:  {description}\nHerkese açık:       {is_public}\nÖzel:      {is_private}\nPush izin:   {allow_push}\nYol:         {path}\nDallar:     {branches}\nEtiketler:         {tags}",
    'cli.bool_yes'             => 'evet',
    'cli.bool_no'              => 'hayır',
    'cli.none_value'           => '(yok)',
    'cli.unknown_command'      => "Bilinmeyen komut: {cmd}\n  Yardım için 'soft-serve help' çalıştırın.",
    'cli.help'                 => "CandyServe — Kendi Barındırdığınız Git Sunucusu\n\nKullanım:\n  soft-serve <komut> [seçenekler]\n\nKomutlar:\n  serve [--config path]    Git sunucusunu başlat\n  init [data-path]         Yeni bir veri dizini başlat\n  user add <username>      Kullanıcı oluştur\n  user key <username> <file>  Kullanıcı için SSH açık anahtarı ekle (stdin için - kullanın)\n  user list                Kullanıcıları listele\n  repo list [data-path]    Depoları listele\n  repo create <name> [data-path]  Depo oluştur\n  repo info <name> [data-path]    Depo bilgilerini göster\n  help, --help, -h         Bu yardımı göster\n  version, --version, -v   Sürümü göster\n\nOrtam Değişkenleri:\n  CANDY_SERVE_DATA_PATH    Veri dizini (varsayılan: /tmp/candy-serve)\n\nÖrnekler:\n  soft-serve init ~/candy-serve-data\n  soft-serve serve --config ~/candy-serve-data/config.yaml\n  soft-serve repo create proje-adi\n  soft-serve user key alice ~/.ssh/id_ed25519.pub",
];
