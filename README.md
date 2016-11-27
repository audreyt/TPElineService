###臺北市政府LINE服務

1.	專案說明

	臺北市政府為貼近民眾需求，推出「防汛資訊訂閱」及「空氣盒子資訊訂閱」服務，提供防汛及空氣盒子資訊的查詢與訂閱，民眾只要加入臺北市政府官方LINE帳號，即可進行訂閱，即時掌握住家或公司附近的防汛與空氣盒子狀況。未來本府也會根據民眾的意見回饋，提供更精確的服務，陸續推出更多與民眾切身相關的生活資訊訂閱。此外，本府也以公開的態度開放原始碼，歡迎大眾一同開發及共享，透過政府及民眾協同合作，創造出更臻完善的方案。
2.	開發環境

	2-1.	部屬環境

	|項目|版本|
	|---|---|
	|php|5.4+|
	|MySQL|5.6|
	|Apache|2.4+|
	|CentOS|7+|

	2-2.	第三方函數庫

	|項目|版本|
	|---|---|
	|Line-JavaScript sdk||
	|jQuery|[2.2.4](https://code.jquery.com/jquery-2.2.4.min.js)|
	|MobileDetect|[2.8.22](http://mobiledetect.net/)|
	|Google Map JavaScript API|v3|
  
	2-3. 其他說明
	
	php需安裝php-xml, php-pdo, php-mbstring
3.	功能項目

 3-1.	擷取:執行抓取[NCDR](https://alerts.ncdr.nat.gov.tw/RSS.aspx)**(無啟用)**以及[空氣盒子](http://data.taipei/opendata/datalist/datasetMeta?oid=4ba06157-3854-4111-9383-3b8a188c962a)的資料
 
	3-2.	推播:推播[NCDR](https://alerts.ncdr.nat.gov.tw/RSS.aspx)**(需至NCDR平台申請HTTP推播)**以及[空氣盒子](http://data.taipei/opendata/datalist/datasetMeta?oid=4ba06157-3854-4111-9383-3b8a188c962a)的資訊
 
	3-3.	訂閱:提供 UI 給使用者訂閱推播服務
 
	3-4.	查詢:提供 UI 給使用者查詢災情與空汙資訊
4.	安裝

	如需測試完整功能需有**LINE Business Connect帳號**
	
	4-1.	將此 repo clone 或是下載至本地端的 DocumentRoot 路徑
	
	4-2.	依照config資料夾內的說明將各變數定義補上後即可執行
5. 授權

	詳細請見[LICENSE](LICENSE)檔案
