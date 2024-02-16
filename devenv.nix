{ pkgs, lib, config, ... }:
let
  inherit (lib.attrsets) attrValues genAttrs;
  pcovExtensions = config.languages.php.extensions ++ [ "pcov" ];
  pcov = pkgs.php81.buildEnv {
    extensions = { all, enabled }: with all; enabled ++ attrValues (lib.getAttrs pcovExtensions
    config.languages.php.package.extensions);
    extraConfig = config.languages.php.ini;
  };
in {
    packages = [
        pkgs.git
        pkgs.symfony-cli
        pkgs.jq
        pkgs.phpPackages.composer
        ( pkgs.writeShellScriptBin "php-pcov" ''
          export PHP_INI_SCAN_DIR=''${PHP_INI_SCAN_DIR-'${pcov}/lib'}
          exec -a "$0" "${pcov}/bin/.php-wrapped"  "$@"
        '')
    ];

    languages.php = {
        enable = true;
        version = "8.3";
        extensions = [ "pcov" ];
        ini = ''
            memory_limit = 2G
            display_errors = On
            error_reporting = E_ALL
            xdebug.mode = debug
            xdebug.discover_client_host = 1
            xdebug.client_host = 127.0.0.1
        '';
        fpm.pools.web = lib.mkDefault {
            settings = {
                "clear_env" = "no";
                "pm" = "dynamic";
                "pm.max_children" = 10;
                "pm.start_servers" = 2;
                "pm.min_spare_servers" = 1;
                "pm.max_spare_servers" = 10;
            };
        };
    };

    services.mysql = {
        enable = lib.mkDefault true;
        package = lib.mkDefault pkgs.mysql;
        initialDatabases = [{ name = "sh00p"; }];
        ensureUsers = [
            {
                name = "sh00p";
                password = "sh00p";
                ensurePermissions = {
                    "sh00p.*" = "ALL PRIVILEGES";
                };
            }
        ];
    };

    services.caddy = {
        enable = lib.mkDefault true;

        virtualHosts.":8000" = lib.mkDefault {
            extraConfig = lib.mkDefault ''
                root * public
                php_fastcgi unix/${config.languages.php.fpm.pools.web.socket}
                file_server
            '';
        };
    };

    services.adminer.enable = lib.mkDefault true;
    services.adminer.listen = lib.mkDefault "127.0.0.1:9080";
    # Redis könnte man einbinden für Session Managment: Overkill für dieses Projekt

    env.APP_URL = lib.mkDefault "http://localhost:8000";
    env.APP_SECRET = lib.mkDefault "secretfordev";
    env.DATABASE_URL = lib.mkDefault "mysql://root@localhost:3306/sh00p";

    dotenv.disableHint = true;
}
