CREATE TABLE IF NOT EXISTS "tbl_channels" (
	"i_Id" int(11) PRIMARY KEY NOT NULL,
	"s_Name" varchar(200) NOT NULL,
	"i_CreatorId" int(11) NOT NULL,
	"i_Type" tinyint(1) NOT NULL DEFAULT(0),
	"b_Authorized" tinyint(1) NOT NULL DEFAULT(0),
	"b_Disabled" tinyint(1) NOT NULL DEFAULT(0),
	"t_Options" text NOT NULL
);

CREATE TABLE IF NOT EXISTS "tbl_user" (
	"i_Id" int(11) PRIMARY KEY NOT NULL,
	"s_UserName" varchar(200) NOT NULL,
	"s_Password" varchar(60) NOT NULL,
	"s_Email" varchar(200) NOT NULL,
	"i_Gender" int(11) NOT NULL DEFAULT(0),
	"s_FirstName" varchar(200) NOT NULL,
	"s_LastName" varchar(200) NOT NULL
);