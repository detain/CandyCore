<?php

/**
 * French translations for candy-serve.
 *
 * @return array<string, string>
 */

declare(strict_types=1);

return [
    'user.invalid_ssh_key'     => 'Format de clé SSH publique invalide',
    'repo.create_dir_failed'   => 'Échec de la création du répertoire du dépôt : {path}',
    'repo.git_init_failed'     => 'Échec de git init : {output}',
    'config.not_found'         => 'Fichier de configuration introuvable : {path}',
    'config.read_failed'       => 'Échec de la lecture de la configuration : {path}',
    'ssh.user_cannot_create'   => 'L\'utilisateur {viewer} ne peut pas créer de dépôts',

    // bin/soft-serve — banner + status output
    'cli.banner'               => 'CandyServe v{version}',
    'cli.config_summary'       => "Data path:  {data_path}\nSSH:        {ssh_addr}\nHTTP:       {http_addr}\nGit daemon: {git_addr}",
    'cli.starting_servers'     => 'Démarrage des serveurs...',
    'cli.note_ssh2_required'   => '(Le mode daemon complet nécessite l\'extension ssh2 et un démon SSH en cours.)',
    'cli.note_run_init'        => "(Utilisez 'soft-serve init' pour initialiser d'abord votre répertoire de données.)",
    'cli.repos_header'         => 'Dépôts :',
    'cli.repo_listing_entry'   => '  - {name}',
    'cli.repo_listing_plain'   => '  {name}',
    'cli.note_not_a_daemon'    => 'Pas encore en mode daemon (le mode daemon nécessite un gestionnaire de processus.)',
    'cli.note_http_help'       => 'Pour servir Git sur HTTP, pointez votre serveur web vers ce script.',
    'cli.note_ssh_help'        => 'Pour l\'accès SSH, utilisez un tunnel inverse ou configurez sshd avec ForceCommand.',

    // bin/soft-serve — init
    'cli.already_initialized'  => 'Déjà initialisé : {path}',
    'cli.initializing'         => 'Initialisation du répertoire de données CandyServe : {path}',
    'cli.done'                 => 'Terminé.',
    'cli.next_steps'           => 'Étapes suivantes :',
    'cli.next_step_1'          => '  1. Modifiez {path}/config.yaml',
    'cli.next_step_2'          => '  2. Générez une clé d\'hôte SSH : ssh-keygen -t ed25519 -f {path}/ssh/soft_serve_host',
    'cli.next_step_3'          => '  3. Définissez CANDY_SERVE_INITIAL_ADMIN_KEYS=votre-clé-publique-ssh',
    'cli.next_step_4'          => '  4. Exécutez : soft-serve serve --config {path}/config.yaml',

    // bin/soft-serve — user
    'cli.usage_user_root'      => 'Usage : soft-serve user add|key|list',
    'cli.usage_user_add'       => 'Usage : soft-serve user add <username>',
    'cli.usage_user_key'       => 'Usage : soft-serve user key <username> [key-file]',
    'cli.user_created'         => "Utilisateur '{username}' créé (admin : true)",
    'cli.user_key_hint'        => "Utilisez 'soft-serve user key {username} < key.pub' pour ajouter une clé SSH publique.",
    'cli.user_key_read_failed' => 'Échec de la lecture de la clé depuis : {file}',
    'cli.user_key_added'       => "Clé ajoutée pour l'utilisateur '{username}'",
    'cli.user_keys_header'     => 'Clés autorisées :',
    'cli.user_key_entry'       => '  {prefix}...',
    'cli.user_list_empty'      => "Utilisateurs :\n  (Aucun utilisateur enregistré. Utilisez 'soft-serve user add <username>')",

    // bin/soft-serve — repo
    'cli.usage_repo_root'      => 'Usage : soft-serve repo list|create|info',
    'cli.usage_repo_create'    => 'Usage : soft-serve repo create <name>',
    'cli.usage_repo_info'      => 'Usage : soft-serve repo info <name>',
    'cli.no_repos'             => 'Aucun dépôt pour le moment.',
    'cli.repo_listing_none'    => '  (aucun dépôt pour le moment)',
    'cli.repo_invalid_name'    => 'Nom de dépôt invalide : {name} (utilisez uniquement alphanumérique, point, tiret bas, tiret)',
    'cli.repo_created'         => "Dépôt '{name}' créé à {path}",
    'cli.repo_clone_url'       => 'URL de clone : ssh://localhost:23231/{name}',
    'cli.repo_not_found'       => 'Dépôt introuvable : {name}',
    'cli.repo_info'            => "Nom :         {name}\nDescription :  {description}\nPublic :       {is_public}\nPrivé :      {is_private}\nAutoriser push : {allow_push}\nChemin :         {path}\nBranches :     {branches}\nTags :         {tags}",
    'cli.bool_yes'             => 'oui',
    'cli.bool_no'              => 'non',
    'cli.none_value'           => '(aucune)',

    // bin/soft-serve — help / unknown
    'cli.unknown_command'      => "Commande inconnue : {cmd}\n  Exécutez 'soft-serve help' pour l'aide.",
    'cli.help'                 => "CandyServe — Serveur Git auto-hébergeable\n\nUsage :\n  soft-serve <commande> [options]\n\nCommandes :\n  serve [--config path]    Démarrer le serveur Git\n  init [data-path]         Initialiser un nouveau répertoire de données\n  user add <username>      Créer un utilisateur\n  user key <username> <file>  Ajouter une clé SSH publique pour l'utilisateur (utilisez - pour stdin)\n  user list                Lister les utilisateurs\n  repo list [data-path]    Lister les dépôts\n  repo create <name> [data-path]  Créer un dépôt\n  repo info <name> [data-path]    Afficher les informations d'un dépôt\n  help, --help, -h         Afficher cette aide\n  version, --version, -v   Afficher la version\n\nEnvironnement :\n  CANDY_SERVE_DATA_PATH    Répertoire de données (défaut : /tmp/candy-serve)\n\nExemples :\n  soft-serve init ~/candy-serve-data\n  soft-serve serve --config ~/candy-serve-data/config.yaml\n  soft-serve repo create mon-projet\n  soft-serve user key alice ~/.ssh/id_ed25519.pub",
];
