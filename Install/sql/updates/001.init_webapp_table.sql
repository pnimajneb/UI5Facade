CREATE TABLE IF NOT EXISTS `fiori_webapp` (
  `oid` binary(16) NOT NULL,
  `created_on` datetime NOT NULL,
  `modified_on` datetime NOT NULL,
  `created_by_user_oid` binary(16) DEFAULT NULL,
  `modified_by_user_oid` binary(16) DEFAULT NULL,
  `app_id` varchar(70) NOT NULL COMMENT 'Unique identifier of the app, which must correspond to the component name',
  `app_title` varchar(100) NOT NULL COMMENT 'The entry is language-dependent and specified via {{…}} syntax',
  `app_subTitle` varchar(100) DEFAULT NULL COMMENT 'Language-dependent entry for a subtitle; specified via {{...}} syntax',
  `app_shortTitle` varchar(100) DEFAULT NULL COMMENT 'Short version of the title. Language-dependent entry has to be specified via {{...}} syntax',
  `app_info` varchar(100) DEFAULT NULL COMMENT 'Needed for CDM (Common Data Model) conversion of tiles. Language-dependent entry has to be specified via {{...}} syntax',
  `app_description` varchar(200) DEFAULT NULL COMMENT 'Language-dependent entry that is specified via {{…}} syntax',
  `name` varchar(100) NOT NULL COMMENT 'Language-independent project name',
  `root_page_alias` varchar(128) NOT NULL,
  `current_version` varchar(10) DEFAULT NULL,
  `current_version_date` datetime DEFAULT NULL,
  `ui5_min_version` varchar(5) NOT NULL,
  `ui5_source` varchar(100) NOT NULL COMMENT 'URI of the UI5 sources relative to site root or absolute',
  `ui5_theme` varchar(50) NOT NULL COMMENT 'Theme to use in the exported app: e.g. sap_belize',
  `ui5_app_control` varchar(50) NOT NULL COMMENT 'Qualified name of the app control to be used: e.g. sap.m.App'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;