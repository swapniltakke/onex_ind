SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tbl_transactions](
	[tr_id] [int] NOT NULL,
	[product_id] [int] NULL,
	[product_name] [varchar](100) NULL,
	[user_id] [int] NULL,
	[cust_name] [varchar](200) NULL,
	[serial_no] [int] NULL,
	[panel_no] [int] NULL,
	[station_id] [int] NULL,
	[station_name] [varchar](200) NULL,
	[start_time] [datetime2](0) NULL,
	[end_date] [datetime2](0) NULL,
	[barcode] [varchar](200) NULL,
	[stage_id] [int] NULL
) ON [PRIMARY]
GO
ALTER TABLE [dbo].[tbl_transactions] ADD PRIMARY KEY CLUSTERED 
(
	[tr_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
ALTER TABLE [dbo].[tbl_transactions] ADD  DEFAULT (NULL) FOR [product_id]
GO
ALTER TABLE [dbo].[tbl_transactions] ADD  DEFAULT (NULL) FOR [product_name]
GO
ALTER TABLE [dbo].[tbl_transactions] ADD  DEFAULT (NULL) FOR [user_id]
GO
ALTER TABLE [dbo].[tbl_transactions] ADD  DEFAULT (NULL) FOR [cust_name]
GO
ALTER TABLE [dbo].[tbl_transactions] ADD  DEFAULT (NULL) FOR [serial_no]
GO
ALTER TABLE [dbo].[tbl_transactions] ADD  DEFAULT (NULL) FOR [panel_no]
GO
ALTER TABLE [dbo].[tbl_transactions] ADD  DEFAULT (NULL) FOR [station_id]
GO
ALTER TABLE [dbo].[tbl_transactions] ADD  DEFAULT (NULL) FOR [station_name]
GO
ALTER TABLE [dbo].[tbl_transactions] ADD  DEFAULT (NULL) FOR [start_time]
GO
ALTER TABLE [dbo].[tbl_transactions] ADD  DEFAULT (NULL) FOR [end_date]
GO
ALTER TABLE [dbo].[tbl_transactions] ADD  DEFAULT (NULL) FOR [barcode]
GO

