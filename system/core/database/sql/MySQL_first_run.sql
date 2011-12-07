CREATE TABLE `sb_database_version` (
  `actual_version` integer  NOT NULL DEFAULT 0,
  `previous_version` integer  NOT NULL DEFAULT 0
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;