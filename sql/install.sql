--
-- Cache table for Transifex strings and string counter.
-- NB: index_hash_res_lang does not need to include the context because it's already in the hash.
--
CREATE TABLE `civicrm_translationhelper_cache` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique record ID',
  `string_key` text NOT NULL COMMENT 'Original string key',
  `string_hash` VARCHAR(32) NOT NULL COMMENT 'MD5 hash of the key',
  `translation` text DEFAULT NULL COMMENT 'String translation',
  `context` varchar(32) COLLATE utf8_unicode_ci DEFAULT '' COMMENT 'String gettext context',
  `resource` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Transifex resource',
  `domain` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Gettext domain',
  `language` varchar(8) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Translation language',
  `count` int(10) unsigned DEFAULT 0 COMMENT 'Number of times the strings was seen, if logging.',
  PRIMARY KEY (`id`),
  KEY `index_hash_lang` (`string_hash`, `resource`, `language`),
  KEY `index_hash_res_lang` (`string_hash`, `language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
