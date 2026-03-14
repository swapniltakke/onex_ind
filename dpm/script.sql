CREATE TABLE tbl_product (
  [product_id] int IDENTITY(1,1) PRIMARY KEY NOT NULL,
  [product_name] varchar(20) DEFAULT NULL,
  [description] varchar(30) DEFAULT NULL,
  [create_dtts] datetime2(0) DEFAULT NULL,
  [ud_dtts] datetime2(0) DEFAULT NULL
) ;

CREATE TABLE tbl_stage (
  [stage_id] int IDENTITY(1,1) PRIMARY KEY NOT NULL,
  [stage_name] varchar(60) DEFAULT NULL,
  [description] varchar(200) DEFAULT NULL,
  [stage_type] varchar(10) NOT NULL CHECK (stage_type IN('Assembly', 'Testing'))
) ;


CREATE TABLE tbl_workbench (
  [id] int IDENTITY(1,1) PRIMARY KEY NOT NULL,
  [title] varchar(30) DEFAULT NULL,
  [title_icon] varchar(200) DEFAULT NULL
) ;

CREATE TABLE tbl_checklist (
  [id] int IDENTITY(1,1) PRIMARY KEY NOT NULL,
  [checklist_item] varchar(300) DEFAULT NULL,
  [description] varchar(200) DEFAULT NULL
) ;


CREATE TABLE tbl_user_login (
  [user_id] int IDENTITY(1,1) PRIMARY KEY NOT NULL,
  [user_string] varchar(200) DEFAULT NULL,
  [user_name] varchar(8) DEFAULT NULL,
  [password] varchar(100) DEFAULT NULL,
  [role_id] int DEFAULT NULL
) ;


CREATE TABLE tbl_roles (
  [role_id] int IDENTITY(1,1) PRIMARY KEY NOT NULL,
  [role_name] varchar(30) DEFAULT NULL
) ;

CREATE TABLE [dbo].[tbl_station](
	[station_id] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL,
	[station_name] [varchar](200) NULL,
	[stage_id] [varchar](200) NOT NULL,
	[product_id] [int] NULL,
	[Machine_name] [varchar](50) NULL
) 


CREATE TABLE [dbo].[tbl_checklistdetails](
  [check_id] [int] IDENTITY(1,1) PRIMARY KEY NOT NULL
	[stage_id] [int] NULL,
	[checklist_id] [int] NULL
) 
