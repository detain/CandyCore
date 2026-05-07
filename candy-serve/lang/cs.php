<?php

/**
 * Czech translations for candy-serve.
 *
 * @return array<string, string>
 */

declare(strict_types=1);

return [
    'user.invalid_ssh_key'     => 'Neplatný formát veřejného klíče SSH',
    'repo.create_dir_failed'   => 'Nelze vytvořit adresář repozitáře: {path}',
    'repo.git_init_failed'     => 'git init selhalo: {output}',
    'config.not_found'         => 'Konfigurační soubor nenalezen: {path}',
    'config.read_failed'       => 'Nelze číst konfiguraci: {path}',
    'ssh.user_cannot_create'   => 'Uživatel {viewer} nemůže vytvářet repozitáře',
    'cli.banner'               => 'CandyServe v{version}',
    'cli.config_summary'       => "Data path:  {data_path}\nSSH:        {ssh_addr}\nHTTP:       {http_addr}\nGit daemon: {git_addr}",
    'cli.starting_servers'     => 'Spouštění serverů...',
    'cli.note_ssh2_required'   => '(Plný daemon režim vyžaduje ssh2 rozšíření a běžící SSH daemon.)',
    'cli.note_run_init'        => "(Nejprve spusťte 'soft-serve init' pro inicializaci datového adresáře.)",
    'cli.repos_header'         => 'Repozitáře:',
    'cli.repo_listing_entry'   => '  - {name}',
    'cli.repo_listing_plain'   => '  {name}',
    'cli.note_not_a_daemon'    => 'Ještě není spuštěn jako daemon (daemon režim vyžaduje správce procesů.)',
    'cli.note_http_help'       => 'Pro servírování Git přes HTTP namiřte web server na tento skript.',
    'cli.note_ssh_help'        => 'Pro SSH přístup použijte reverzní tunel nebo nakonfigurujte sshd s ForceCommand.',
    'cli.already_initialized'  => 'Již inicializováno: {path}',
    'cli.initializing'         => 'Inicializace datového adresáře CandyServe: {path}',
    'cli.done'                 => 'Hotovo.',
    'cli.next_steps'           => 'Další kroky:',
    'cli.next_step_1'          => '  1. Upravte {path}/config.yaml',
    'cli.next_step_2'          => '  2. Vygenerujte SSH host klíč: ssh-keygen -t ed25519 -f {path}/ssh/soft_serve_host',
    'cli.next_step_3'          => '  3. Nastavte CANDY_SERVE_INITIAL_ADMIN_KEYS=váš-ssh-veřejný-klíč',
    'cli.next_step_4'          => '  4. Spusťte: soft-serve serve --config {path}/config.yaml',
    'cli.usage_user_root'      => 'Použití: soft-serve user add|key|list',
    'cli.usage_user_add'       => 'Použití: soft-serve user add <username>',
    'cli.usage_user_key'       => 'Použití: soft-serve user key <username> [key-file]',
    'cli.user_created'         => "Uživatel '{username}' vytvořen (admin: true)",
    'cli.user_key_hint'        => "Pro přidání veřejného klíče SSH použijte 'soft-serve user key {username} < key.pub'.",
    'cli.user_key_read_failed' => 'Nelze číst klíč z: {file}',
    'cli.user_key_added'       => "Klíč přidán pro uživatele '{username}'",
    'cli.user_keys_header'     => 'Autorizované klíče:',
    'cli.user_key_entry'       => '  {prefix}...',
    'cli.user_list_empty'      => "Uživatelé:\n  (Žádní registrovaní uživatelé. Použijte 'soft-serve user add <username>')",
    'cli.usage_repo_root'      => 'Použití: soft-serve repo list|create|info',
    'cli.usage_repo_create'    => 'Použití: soft-serve repo create <name>',
    'cli.usage_repo_info'      => 'Použití: soft-serve repo info <name>',
    'cli.no_repos'             => 'Zatím žádné repozitáře.',
    'cli.repo_listing_none'    => '  (zatím žádné repozitáře)',
    'cli.repo_invalid_name'    => 'Neplatný název repozitáře: {name} (používejte pouze alfanumerické, tečku, podtržítko, pomlčku)',
    'cli.repo_created'         => "Repozitář '{name}' vytvořen v {path}",
    'cli.repo_clone_url'       => 'Clone URL: ssh://localhost:23231/{name}',
    'cli.repo_not_found'       => 'Repozitář nenalezen: {name}',
    'cli.repo_info'            => "Název:         {name}\nPopis:  {description}\nVeřejný:       {is_public}\nSoukromý:      {is_private}\nPovolit push:   {allow_push}\nCesta:         {path}\nVětve:     {branches}\nTagy:         {tags}",
    'cli.bool_yes'             => 'ano',
    'cli.bool_no'              => 'ne',
    'cli.none_value'           => '(žádné)',
    'cli.unknown_command'      => "Neznámý příkaz: {cmd}\n  Spusťte 'soft-serve help' pro nápovědu.",
    'cli.help'                 => "CandyServe — Samo-hostovaný Git server\n\nPoužití:\n  soft-serve <příkaz> [volby]\n\nPříkazy:\n  serve [--config path]    Spustit Git server\n  init [data-path]         Inicializovat nový datový adresář\n  user add <username>      Vytvořit uživatele\n  user key <username> <file>  Přidat veřejný klíč SSH pro uživatele (použijte - pro stdin)\n  user list                Seznam uživatelů\n  repo list [data-path]    Seznam repozitářů\n  repo create <name> [data-path]  Vytvořit repozitář\n  repo info <name> [data-path]    Zobrazit info repozitáře\n  help, --help, -h         Zobrazit tuto nápovědu\n  version, --version, -v   Zobrazit verzi\n\nProměnné prostředí:\n  CANDY_SERVE_DATA_PATH    Datový adresář (výchozí: /tmp/candy-serve)\n\nPříklady:\n  soft-serve init ~/candy-serve-data\n  soft-serve serve --config ~/candy-serve-data/config.yaml\n  soft-serve repo create muj-projekt\n  soft-serve user key alice ~/.ssh/id_ed25519.pub",
];
