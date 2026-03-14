SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tbl_product](
	[product_id] [int] IDENTITY(1,1) NOT NULL,
	[product_name] [varchar](20) NULL,
	[description] [varchar](30) NULL,
	[create_dtts] [datetime2](0) NULL,
	[ud_dtts] [datetime2](0) NULL
) ON [PRIMARY]
GO
ALTER TABLE [dbo].[tbl_product] ADD PRIMARY KEY CLUSTERED 
(
	[product_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
ALTER TABLE [dbo].[tbl_product] ADD  DEFAULT (NULL) FOR [product_name]
GO
ALTER TABLE [dbo].[tbl_product] ADD  DEFAULT (NULL) FOR [description]
GO
ALTER TABLE [dbo].[tbl_product] ADD  DEFAULT (NULL) FOR [create_dtts]
GO
ALTER TABLE [dbo].[tbl_product] ADD  DEFAULT (NULL) FOR [ud_dtts]
GO

