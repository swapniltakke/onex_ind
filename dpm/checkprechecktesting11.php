select count(*) as cntqr from tbl_leakrate_save where leak_date1 = '$batch_date' and leak_time between 
SUBTIME('$batch_time_new','00:00:05') and ADDTIME('$batch_time_new','00:00:05')

select count(*)  as cntqr from tbl_leakrate_save where leak_date1 = '2021-12-17' and leak_time between SUBTIME('13:33:49','00:00:05') and ADDTIME('13:33:49','00:00:05')

Provider=SQLNCLI11;Data Source=NITINPATIL\MSSQLSERVER1;Password=VossCutting;User ID=Voss;Initial Catalog=Voss_Cutting
00038318 3AE55642AE400WN2Z 3007430748/110 800005599552
00038318  3AE55642AE400WN2Z  3007430748/110 800005599552

00038318 3AE55642AE400WN2Z 3007430748/110 800005599552
00038318  3AH01234AE400WN2Z  3007430748/110  800005599552
3AE55541,3AE55542,3AE55642,3AE55643

select count(*) as total from tbl_station where concat(',', product_id, ',') like '%,1,%' and concat(',', stage_id, ',') like '%,3,%' and Machine_name = '192.168.100.11'