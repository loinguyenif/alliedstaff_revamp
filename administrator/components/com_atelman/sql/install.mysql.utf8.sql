CREATE TABLE IF NOT EXISTS `#__at_companies` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`customer_id` INT NULL  DEFAULT 0,
`company_name` VARCHAR(191)  NULL  DEFAULT "",
`ordering` INT(11)  NULL  DEFAULT 0,
`state` TINYINT(1)  NULL  DEFAULT 0,
`checked_out` INT(11)  UNSIGNED,
`checked_out_time` DATETIME NULL  DEFAULT NULL ,
`created_by` INT(11)  NULL  DEFAULT 0,
PRIMARY KEY (`id`)
,KEY `idx_state` (`state`)
,KEY `idx_checked_out` (`checked_out`)
,KEY `idx_created_by` (`created_by`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__at_countries` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`country` VARCHAR(191)  NOT NULL ,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__at_endusers` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`customer_id` VARCHAR(100)  NULL  DEFAULT "",
`username` VARCHAR(200)  NULL  DEFAULT "",
`code` VARCHAR(200)  NULL  DEFAULT "",
`ship_enduser_id` VARCHAR(200)  NULL  DEFAULT "",
`ship_name` VARCHAR(200)  NULL  DEFAULT "",
`ship_address1` VARCHAR(200)  NULL  DEFAULT "",
`ship_address2` VARCHAR(200)  NULL  DEFAULT "",
`ship_address3` VARCHAR(200)  NULL  DEFAULT "",
`ship_zip` VARCHAR(200)  NULL  DEFAULT "",
`ship_city` VARCHAR(200)  NULL  DEFAULT "",
`ship_country` VARCHAR(200)  NULL  DEFAULT "",
`ship_telephone` VARCHAR(200)  NULL  DEFAULT "",
`ship_fax` VARCHAR(200)  NULL  DEFAULT "",
`ship_email` VARCHAR(200)  NULL  DEFAULT "",
`contact_name` VARCHAR(200)  NULL  DEFAULT "",
`contact_telephone` VARCHAR(200)  NULL  DEFAULT "",
`contact_mobile` VARCHAR(200)  NULL  DEFAULT "",
`contact_fax` VARCHAR(200)  NULL  DEFAULT "",
`contact_email` VARCHAR(200)  NULL  DEFAULT "",
`sbmt_later` TEXT(255)  NULL ,
`created_date` DATETIME NULL  DEFAULT NULL ,
`lastVisit` DATETIME NULL  DEFAULT NULL ,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__at_endusers_counter` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`enduser_var` VARCHAR(100)  NULL  DEFAULT "",
`counter` INT NULL  DEFAULT 0,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__atel_at_groups` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`name` VARCHAR(240)  NULL  DEFAULT "",
`created_date` DATETIME NULL  DEFAULT NULL ,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__at_group_access` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`group_id` INT NULL  DEFAULT 0,
`access` VARCHAR(200)  NULL  DEFAULT "",
PRIMARY KEY (`id`)
,KEY `idx_access` (`access`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__at_group_country_xref` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`group_id` INT NULL  DEFAULT 0,
`country_id` INT NULL  DEFAULT 0,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__at_group_distributor_xref` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`group_id` INT NULL  DEFAULT 0,
`user_id` INT(11)  NULL  DEFAULT 0,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__at_group_xref` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`group_id` INT NULL  DEFAULT 0,
`user_id` INT(11)  NULL  DEFAULT 0,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__at_logs` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`section` VARCHAR(200)  NULL  DEFAULT "",
`action_type` VARCHAR(200)  NULL  DEFAULT "",
`action_by` INT NULL  DEFAULT 0,
`which_id` INT NULL  DEFAULT 0,
`remarks` TEXT(255)  NULL ,
`before_update` DATETIME NULL  DEFAULT NULL ,
`after_update` DATETIME NULL  DEFAULT NULL ,
`action_date` DATETIME NULL  DEFAULT NULL ,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__at_posno` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`section` VARCHAR(10)  NULL  DEFAULT "",
`prefix` VARCHAR(10)  NULL  DEFAULT "",
`position_no` INT NULL  DEFAULT 0,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__at_products` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`product_no` VARCHAR(240)  NULL  DEFAULT "",
`model_no` VARCHAR(240)  NULL  DEFAULT "",
`product_name` VARCHAR(240)  NULL  DEFAULT "",
`warranty` INT NULL  DEFAULT 0,
`is_previous3years` TINYINT(1)  NULL  DEFAULT 0,
`created_date` DATETIME NULL  DEFAULT NULL ,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__at_rma_downloads` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`rma_item_id` INT NULL  DEFAULT 0,
`status` VARCHAR(200)  NULL  DEFAULT "",
`filename` TEXT(255)  NULL ,
`is_airway_bill` TINYINT NULL  DEFAULT 0,
`uploaded_by` INT NULL  DEFAULT 0,
`created_date` DATETIME NULL  DEFAULT NULL ,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__at_rma_items` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`rma_request_id` INT NULL  DEFAULT 0,
`rmacode` VARCHAR(240)  NULL  DEFAULT "",
`warranty_item_id` INT NULL  DEFAULT 0,
`product_id` INT NULL  DEFAULT 0,
`customer_id` VARCHAR(200)  NULL  DEFAULT "",
`requested_sn` VARCHAR(200)  NULL  DEFAULT "",
`replacement_pn` VARCHAR(100)  NULL  DEFAULT "",
`replacement_sn` VARCHAR(200)  NULL  DEFAULT "",
`warranty_status` VARCHAR(10)  NULL  DEFAULT "",
`description` TEXT(255)  NULL ,
`status` VARCHAR(50)  NULL  DEFAULT "",
`shipping_duration` INT NULL  DEFAULT 0,
`so_no` VARCHAR(100)  NULL  DEFAULT "",
`invoice_no` VARCHAR(240)  NULL  DEFAULT "",
`remarks` TEXT(255)  NULL ,
`replacement_date` DATETIME NULL  DEFAULT NULL ,
`rma_assigned_date` DATETIME NULL  DEFAULT NULL ,
`received_date` DATETIME NULL  DEFAULT NULL ,
`shipped_date` DATETIME NULL  DEFAULT NULL ,
`closed_date` DATETIME NULL  DEFAULT NULL ,
`created_date` DATETIME NULL  DEFAULT NULL ,
`is_import_csv` TINYINT NULL  DEFAULT 0,
`created_by` INT(11)  NULL  DEFAULT 0,
PRIMARY KEY (`id`)
,KEY `idx_created_by` (`created_by`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__at_rma_request` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`user_id` INT(11)  NULL  DEFAULT 0,
`fullname` VARCHAR(200)  NULL  DEFAULT "",
`contact_name` VARCHAR(200)  NULL  DEFAULT "",
`address` TEXT(255)  NULL ,
`city` VARCHAR(200)  NULL  DEFAULT "",
`state` VARCHAR(200)  NULL  DEFAULT "",
`postal_code` VARCHAR(200)  NULL  DEFAULT "",
`country` VARCHAR(200)  NULL  DEFAULT "",
`telephone` VARCHAR(200)  NULL  DEFAULT "",
`fax` VARCHAR(200)  NULL  DEFAULT "",
`email` VARCHAR(200)  NULL  DEFAULT "",
`created_date` DATETIME NULL  DEFAULT NULL ,
PRIMARY KEY (`id`)
,KEY `idx_state` (`state`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__at_rma_status` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`status_code` VARCHAR(100)  NULL  DEFAULT "",
`status_name` VARCHAR(240)  NULL  DEFAULT "",
`ordering` INT NULL  DEFAULT 0,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__at_service_contract` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`user_id` INT(11)  NULL  DEFAULT 0,
`service_type` VARCHAR(100)  NULL  DEFAULT "",
`expiry_date` DATETIME NULL  DEFAULT NULL ,
`service_contract_no` VARCHAR(100)  NULL  DEFAULT "",
`cover_length` VARCHAR(100)  NULL  DEFAULT "",
`po_no` VARCHAR(30)  NULL  DEFAULT "",
`client_name` VARCHAR(200)  NULL  DEFAULT "",
`customer_id` VARCHAR(50)  NULL  DEFAULT "",
`remarks` TEXT(255)  NULL ,
`reminder1` TINYINT(1)  NULL  DEFAULT 0,
`start_date` DATETIME NULL  DEFAULT NULL ,
`created_date` DATETIME NULL  DEFAULT NULL ,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__at_service_contract_product_xref` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`service_contract_id` INT NULL  DEFAULT 0,
`warranty_id` INT NULL  DEFAULT 0,
`serial_no` VARCHAR(100)  NULL  DEFAULT "",
`model_no` VARCHAR(50)  NULL  DEFAULT "",
`part_no` VARCHAR(50)  NULL  DEFAULT "",
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__at_warranty_history` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`warranty_id` INT NULL  DEFAULT 0,
`serial_no_2` VARCHAR(200)  NULL  DEFAULT "",
`replacement_pn` VARCHAR(200)  NULL  DEFAULT "",
`created_date` DATETIME NULL  DEFAULT NULL ,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__at_warranty_items` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`warranty_id` INT NULL  DEFAULT 0,
`customer_id` VARCHAR(200)  NULL  DEFAULT "",
`product_id` INT NULL  DEFAULT 0,
`serial_no` VARCHAR(240)  NULL  DEFAULT "",
`serial_no_2` VARCHAR(240)  NULL  DEFAULT "",
`replacement_pn` VARCHAR(200)  NULL  DEFAULT "",
`po_no` VARCHAR(200)  NULL  DEFAULT "",
`so_no` VARCHAR(200)  NULL  DEFAULT "",
`invoice_no` VARCHAR(200)  NULL  DEFAULT "",
`purchase_date` DATETIME NULL  DEFAULT NULL ,
`comments` TEXT(255)  NULL ,
`expired_date` DATETIME NULL  DEFAULT NULL ,
`expired_date_manual` DATETIME NULL  DEFAULT NULL ,
`extended_warranty` INT NULL  DEFAULT 0,
`extended_expired_date` DATETIME NULL  DEFAULT NULL ,
`created_date` DATETIME NULL  DEFAULT NULL ,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__at_warranty_register` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`first_name` VARCHAR(240)  NULL  DEFAULT "",
`last_name` VARCHAR(240)  NULL  DEFAULT "",
`address` TEXT(255)  NULL ,
`city` VARCHAR(240)  NULL  DEFAULT "",
`postal_code` VARCHAR(240)  NULL  DEFAULT "",
`country` VARCHAR(100)  NULL  DEFAULT "",
`telephone` VARCHAR(240)  NULL  DEFAULT "",
`fax` VARCHAR(240)  NULL  DEFAULT "",
`email` VARCHAR(240)  NULL  DEFAULT "",
`company_name` VARCHAR(240)  NULL  DEFAULT "",
`job_title` VARCHAR(240)  NULL  DEFAULT "",
`created_date` DATETIME NULL  DEFAULT NULL ,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__at_world_countries` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`country_code` VARCHAR(3)  NULL  DEFAULT "",
`country_name` VARCHAR(240)  NULL  DEFAULT "",
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8mb4_unicode_ci;

