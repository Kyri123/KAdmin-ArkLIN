create table ArkAdmin_players (total_id int auto_increment, server text null, id bigint null, SteamId bigint null, SteamName text null, CharacterName text null, Level bigint null, ExperiencePoints bigint null, TotalEngramPoints bigint null, FirstSpawned boolean null, FileCreated bigint null, FileUpdated bigint null, TribeId text null, TribeName longtext null, constraint ArkAdmin_players_pk primary key (total_id));