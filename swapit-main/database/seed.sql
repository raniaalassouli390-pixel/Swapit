SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `reports`;
TRUNCATE TABLE `notifications`;
TRUNCATE TABLE `reviews`;
TRUNCATE TABLE `wishlist`;
TRUNCATE TABLE `messages`;
TRUNCATE TABLE `conversations`;
TRUNCATE TABLE `swap_offers`;
TRUNCATE TABLE `bids`;
TRUNCATE TABLE `auctions`;
TRUNCATE TABLE `listing_images`;
TRUNCATE TABLE `listings`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `categories`;
SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO `categories` (`id`,`name`,`slug`,`icon`,`sort_order`) VALUES
(1,'Electronics','electronics','📱',1),
(2,'Clothing','clothing','👕',2),
(3,'Books','books','📚',3),
(4,'Furniture','furniture','🛋️',4),
(5,'Vehicles','vehicles','🚗',5),
(6,'Sports','sports','⚽',6),
(7,'Collectibles','collectibles','🏺',7),
(8,'Kids & Toys','kids-toys','🧸',8),
(9,'Musical Instruments','music','🎸',9),
(10,'Kitchen','kitchen','🍳',10);

INSERT INTO `users` (`id`,`username`,`first_name`,`last_name`,`email`,`password`,`city`,`bio`,`role`,`status`,`is_verified`,`rating`,`review_count`) VALUES
(1,'admin','Admin','SwapIt','admin@swapit.jo','$2y$10$gMoL/wBaJ0uSdBWs0GWJtO.TIfwaJKMwY6lVAV8WckNQ50kX3MDTC','Amman','SwapIt platform admin.','admin','active',1,5.00,0),
(2,'ahmadkh','Ahmad','Khalil','ahmad@example.com','$2y$10$gMoL/wBaJ0uSdBWs0GWJtO.TIfwaJKMwY6lVAV8WckNQ50kX3MDTC','Amman','Tech enthusiast. Buying and selling gadgets since 2018.','user','active',1,4.90,124),
(3,'saramah','Sara','Mahmoud','sara@example.com','$2y$10$gMoL/wBaJ0uSdBWs0GWJtO.TIfwaJKMwY6lVAV8WckNQ50kX3MDTC','Irbid','Fashion and lifestyle. Always looking for great swaps!','user','active',1,4.70,48),
(4,'omartq','Omar','Tariq','omar@example.com','$2y$10$gMoL/wBaJ0uSdBWs0GWJtO.TIfwaJKMwY6lVAV8WckNQ50kX3MDTC','Zarqa','Gaming and electronics. Quick deals, fair prices.','user','active',1,4.50,32),
(5,'linabk','Lina','Barakat','lina@example.com','$2y$10$gMoL/wBaJ0uSdBWs0GWJtO.TIfwaJKMwY6lVAV8WckNQ50kX3MDTC','Amman','Interior design lover. Selling furniture and home decor.','user','active',1,5.00,87),
(6,'yousefh','Yousef','Hani','yousef@example.com','$2y$10$gMoL/wBaJ0uSdBWs0GWJtO.TIfwaJKMwY6lVAV8WckNQ50kX3MDTC','Irbid','Collector and vintage enthusiast. Great deals guaranteed.','user','active',1,4.90,61),
(7,'ramiad','Rami','Adel','rami@example.com','$2y$10$gMoL/wBaJ0uSdBWs0GWJtO.TIfwaJKMwY6lVAV8WckNQ50kX3MDTC','Aqaba','Sports and outdoors. Selling quality gear at fair prices.','user','active',1,4.60,19),
(8,'noorjs','Noor','Saleh','noor@example.com','$2y$10$gMoL/wBaJ0uSdBWs0GWJtO.TIfwaJKMwY6lVAV8WckNQ50kX3MDTC','Amman','Books, art, and craft supplies. Passionate reader.','user','active',1,4.80,43);

-- Every listing below is written to match the EXACT product shown in its image.
INSERT INTO `listings` (`id`,`user_id`,`category_id`,`title`,`description`,`type`,`price`,`condition`,`city`,`swap_notes`,`status`,`views`,`is_featured`) VALUES
(1,2,1,'Apple Watch Series 9 GPS 45mm Midnight','Apple Watch Series 9, 45mm aluminium case in Midnight with the black sport band shown in the photo. Always-On Retina display, GPS, heart-rate and blood-oxygen sensors. Battery health 100%, screen and case are scratch-free as it lived in a case. Comes with the magnetic charger and both Arabic and English watch faces already set up.','sale',155.00,'like_new','Amman',NULL,'active',268,1),
(2,2,1,'Laptop, Smartphone & Tablet Home-Office Bundle','Complete home-office tech bundle exactly as pictured: a slim silver laptop, a smartphone and a tablet on its stand. All three power on perfectly, hold a charge well and have been reset to factory settings, ready for a new owner. An ideal all-in-one kit for remote work, study or a first setup.','sale',940.00,'like_new','Amman',NULL,'active',401,1),
(3,4,1,'PS5 DualSense Wireless Controller White & Gold','Official-style DualSense wireless controller in white with gold accents, just like the photo. Adaptive triggers and haptic feedback work flawlessly and the thumbsticks have zero drift. Charges over USB-C and pairs with both PS5 and PC. Clean surfaces, no sticker residue or shine wear.','sale',45.00,'like_new','Zarqa',NULL,'active',187,0),
(4,5,1,'Black DSLR Camera with 18-55mm Lens','Black DSLR camera with the 18-55mm zoom lens shown. Crisp APS-C sensor, full-HD video and a comfortable grip. Shutter count is under 9,000. Includes the body cap, neck strap and battery charger. A great first proper camera for anyone moving on from a phone.','sale',290.00,'good','Amman',NULL,'active',162,1),
(5,7,6,'Adjustable Dumbbell & Match Soccer Ball Set','Home-fitness combo exactly as pictured: one chrome adjustable dumbbell plus a size-5 match soccer ball. The dumbbell plates lock on securely and the ball holds air perfectly with even stitching. Everything you need to train indoors during the week and play outside on the weekend.','sale',35.00,'good','Aqaba',NULL,'active',74,0),
(6,3,2,'Premium Cotton Crew-Neck T-Shirt Royal Blue','Brand-new royal-blue crew-neck t-shirt in soft premium cotton, exactly the colour shown. Relaxed unisex fit in size M. Never worn, tags removed but completely unused. Keeps its shape and colour after washing. Smoke-free home.','sale',12.00,'new','Irbid',NULL,'active',58,0),
(7,8,3,'Hardcover Bestseller Book Bundle 3 Titles','Bundle of three hardcover books with the red, blue and orange covers pictured. Pages are clean, spines are tight and there are no markings, tears or missing pages. A lovely little starter shelf or a ready-made gift set for any reader.','sale',22.00,'good','Amman',NULL,'active',49,0),
(8,5,4,'Tan Leather Wingback Chair plus Architect Lamp','Camel-tan leather wingback accent chair on solid wood legs, paired with the black adjustable architect desk lamp shown. The cushions are firm and the leather is clean with no cracks or peeling. The lamp arm moves smoothly and gives a warm light. Pickup from Amman.','sale',210.00,'like_new','Amman',NULL,'active',96,1),
(9,5,4,'Mid-Century Cream Accent Tub Chair','Soft cream upholstered tub chair on tapered wooden legs, exactly like the photo. Comfortable curved back, clean fabric and a sturdy frame with no wobble. A perfect reading-nook or bedroom-corner chair. Pickup only from Amman.','sale',85.00,'good','Amman',NULL,'active',67,0),
(10,8,10,'Freestanding Range Cooker White & Copper 60cm','Stylish 60cm freestanding range cooker in white with copper trim, matching the picture. Glass-top hob, digital oven display and lovely even baking. Includes the two copper-finish pots shown. Barely used and fully working, selling only because of a kitchen remodel.','sale',320.00,'like_new','Amman',NULL,'active',81,0),
(11,3,1,'Smartphone, Smartwatch & Compact Camera Set','Three-piece gadget set exactly as pictured: a smartphone, a smartwatch and a compact camera. Every device has been tested, holds a charge and is reset for its next owner. A neat all-in-one kit for someone who wants to start fresh without buying three things separately.','sale',360.00,'good','Irbid',NULL,'active',204,1),
(12,6,9,'Natural Acoustic Dreadnought Guitar','Full-size natural-wood acoustic guitar exactly as shown. Warm balanced tone, comfortable low action and no cracks or fret buzz. Freshly restrung and ready to play. Beginner-friendly and great for learning at home or around a campfire.','sale',70.00,'good','Irbid',NULL,'active',112,0),
(13,4,2,'Formal Wardrobe Set Shirt, Tie, Handbag & Knits','Curated wardrobe bundle exactly as pictured: a crisp cream dress shirt with a matching gold tie, a soft pink-and-gold handbag and neatly folded knit pieces. All clean and only gently used. A great smart-casual refresh for one buyer.','sale',95.00,'good','Zarqa',NULL,'active',53,0),
(14,3,4,'Leafy Indoor Plant in Stone-Finish Pot Swap','Healthy leafy indoor plant in the stone-finish pot shown. It is thriving, easy to care for and instantly brightens any room or corner. Moving abroad soon so I would rather swap it locally than leave it behind.','swap',NULL,'like_new','Irbid','Looking for: a small bookshelf, framed wall art, or a table lamp.','active',41,0),
(15,8,8,'Wooden Table Easel Watercolour Art Set Swap','Compact wooden table easel with the watercolour palette, brush and a couple of practice paintings shown. Lightly used and all the paints are still intact. A lovely starter art kit for a child or a beginner.','swap',NULL,'like_new','Amman','Looking for: kids books, sketch pads, or craft supplies.','active',33,0),
(16,5,5,'Porsche 911 Carrera Cabriolet Silver LIVE AUCTION','Silver Porsche 911 Carrera cabriolet exactly as pictured, with red leather interior and polished alloy wheels. The soft-top opens and closes perfectly, the engine pulls strong and it comes with full service history. A genuine dream car going to the highest bidder.','auction',NULL,'good','Amman',NULL,'active',1284,1),
(17,6,7,'Vintage London Copper Twin-Bell Alarm Clock AUCTION','Charming vintage-style copper twin-bell alarm clock with a London Roman-numeral face, exactly as shown. Loud reliable ring, smooth wind mechanism and a lovely warm patina on the copper. A real shelf statement piece for collectors.','auction',NULL,'good','Irbid',NULL,'active',176,0),
(18,7,7,'Decorative Hourglass Clock with Golden Sand AUCTION','Striking decorative hourglass with a clock face set into the glass and glowing golden sand, exactly like the picture. The sand flows evenly and the clock keeps good time. A beautiful centrepiece for a desk or mantel.','auction',NULL,'like_new','Aqaba',NULL,'active',221,1),
(19,2,7,'Fine Jewellery Set Diamond, Ruby Ring & Pearls AUCTION','Elegant jewellery set exactly as pictured: a brilliant diamond pendant, a gold ruby ring and a pearl bracelet, presented on a display stand. Stones are bright and securely set and the metal is freshly polished. A standout set for a special occasion.','auction',NULL,'like_new','Amman',NULL,'active',512,1),
(20,8,7,'Vintage Brass Compound Microscope AUCTION','Antique-style brass compound microscope on a heavy stable base, exactly as shown. Smooth focus wheels, clear optics and rotating objective lenses. A handsome collector and educational piece. Bidding starts low so grab a bargain.','auction',NULL,'good','Amman',NULL,'active',198,0),
(21,3,2,'Beige Cabin Suitcase & Matching Clutch Set','Beige hard-shell cabin suitcase with a matching patterned clutch, exactly as pictured. Smooth spinner wheels, a working combination lock and a clean lining inside. A stylish travel duo used on one trip only.','sale',60.00,'like_new','Irbid',NULL,'active',69,0),
(22,7,8,'Baby Clothing Bundle with Teddy & Booties','Adorable baby bundle exactly as shown: knit onesies, denim dungarees, soft booties, a bib and a cuddly teddy bear. Everything is freshly washed and gently used with no stains or holes. A perfect newborn starter set.','sale',25.00,'good','Aqaba',NULL,'active',47,0);

-- Primary image of each listing is the exact photo of that product.
INSERT INTO `listing_images` (`listing_id`,`path`,`is_primary`,`sort_order`) VALUES
(1,'/public/icons/apple_ww.png',1,0),
(2,'/public/icons/Electronic10.png',1,0),
(3,'/public/icons/Gaming.png',1,0),
(4,'/public/icons/camera1.jpeg',1,0),
(5,'/public/icons/sport.png',1,0),
(6,'/public/icons/clothing10.png',1,0),
(7,'/public/icons/book10.png',1,0),
(8,'/public/icons/Furniture.jpeg',1,0),
(9,'/public/icons/furniture10.png',1,0),
(10,'/public/icons/Kitchen4.jpeg',1,0),
(11,'/public/icons/Electronic3.jpeg',1,0),
(12,'/public/icons/guitar1.png',1,0),
(13,'/public/icons/Clothing2.jpeg',1,0),
(14,'/public/icons/Garden1.png',1,0),
(15,'/public/icons/art1.png',1,0),
(16,'/public/icons/Vehicles10.png',1,0),
(17,'/public/icons/clook5.png',1,0),
(18,'/public/icons/Clook3.jpeg',1,0),
(19,'/public/icons/jewelry.png',1,0),
(20,'/public/icons/science&habby1.png',1,0),
(21,'/public/icons/bage1.png',1,0),
(22,'/public/icons/kids.png',1,0);

INSERT INTO `auctions` (`id`,`listing_id`,`start_price`,`current_bid`,`starts_at`,`ends_at`,`status`) VALUES
(1,16,18000.00,24500.00,NOW() - INTERVAL 3 DAY,NOW() + INTERVAL 4 DAY,'live'),
(2,17,15.00,46.00,NOW() - INTERVAL 2 DAY,NOW() + INTERVAL 3 DAY,'live'),
(3,18,20.00,58.00,NOW() - INTERVAL 1 DAY,NOW() + INTERVAL 5 DAY,'live'),
(4,19,300.00,720.00,NOW() - INTERVAL 12 HOUR,NOW() + INTERVAL 6 DAY,'live'),
(5,20,40.00,95.00,NOW() - INTERVAL 2 DAY,NOW() + INTERVAL 3 DAY,'live');

INSERT INTO `bids` (`auction_id`,`user_id`,`amount`,`created_at`) VALUES
(1,3,18500.00,NOW() - INTERVAL 70 HOUR),
(1,4,19800.00,NOW() - INTERVAL 60 HOUR),
(1,2,21000.00,NOW() - INTERVAL 48 HOUR),
(1,7,22600.00,NOW() - INTERVAL 24 HOUR),
(1,3,24500.00,NOW() - INTERVAL 3 HOUR),
(2,4,18.00,NOW() - INTERVAL 40 HOUR),
(2,5,28.00,NOW() - INTERVAL 20 HOUR),
(2,7,46.00,NOW() - INTERVAL 2 HOUR),
(3,3,25.00,NOW() - INTERVAL 20 HOUR),
(3,8,40.00,NOW() - INTERVAL 10 HOUR),
(3,4,58.00,NOW() - INTERVAL 1 HOUR),
(4,3,360.00,NOW() - INTERVAL 10 HOUR),
(4,5,520.00,NOW() - INTERVAL 6 HOUR),
(4,7,720.00,NOW() - INTERVAL 2 HOUR),
(5,3,50.00,NOW() - INTERVAL 44 HOUR),
(5,6,72.00,NOW() - INTERVAL 18 HOUR),
(5,4,95.00,NOW() - INTERVAL 8 HOUR);

INSERT INTO `reviews` (`reviewer_id`,`reviewed_id`,`listing_id`,`rating`,`comment`) VALUES
(3,2,1,5,'Apple Watch was exactly as described, screen flawless and battery like new. Fast response and a smooth meetup. Highly recommend!'),
(4,2,2,5,'The laptop, phone and tablet bundle is incredible value. Everything wiped and working. Ahmad is trustworthy and professional.'),
(5,4,3,5,'DualSense controller is mint, no stick drift at all. Omar replied quickly and the deal was easy.'),
(2,5,4,4,'DSLR works great and the lens is sharp. Lina was honest about the shutter count. Minor delay but a fair deal overall.'),
(3,7,5,5,'Dumbbell and ball set is exactly as pictured and in good shape. Rami was friendly and on time in Aqaba.'),
(8,3,6,5,'Brand-new t-shirt, lovely colour and soft cotton. Sara is a pleasure to deal with.'),
(6,8,7,5,'All three books arrived in excellent condition, well wrapped. Great little bundle from Noor.'),
(7,5,8,5,'The leather chair and lamp look stunning at home. Lina helped arrange pickup. Fantastic seller!'),
(2,8,10,4,'Range cooker is clean and bakes evenly, copper pots were a nice bonus. Smooth handover.'),
(4,6,12,5,'Acoustic guitar plays beautifully and was freshly strung. Yousef really knows his instruments.'),
(5,3,21,5,'Suitcase and clutch set is gorgeous and barely used. Perfect swap-style deal, thank you Sara!'),
(8,7,22,4,'Baby bundle was clean and complete, the teddy is adorable. Rami was quick to reply.');

INSERT INTO `wishlist` (`user_id`,`listing_id`) VALUES
(2,16),(2,18),(2,19),
(3,1),(3,2),(3,16),
(4,4),(4,16),(4,19),
(5,11),(5,19),(5,20),
(6,16),(6,17),(6,18),
(7,16),(7,3),(7,2),
(8,8),(8,12);

INSERT INTO `notifications` (`user_id`,`type`,`title`,`body`,`link`,`is_read`) VALUES
(3,'bid','You have been outbid!','Someone placed a higher bid of 720 JD on the Fine Jewellery Set.','/src/config/pages/listing-detail.php?id=19',0),
(4,'bid','You have been outbid!','Someone placed a higher bid of 46 JD on the Copper Twin-Bell Alarm Clock.','/src/config/pages/listing-detail.php?id=17',0),
(3,'bid','You have been outbid!','Someone placed a higher bid of 58 JD on the Hourglass Clock.','/src/config/pages/listing-detail.php?id=18',0),
(2,'review','New review received!','Omar rated you 5 stars for the home-office tech bundle.','/src/config/pages/profile.php?id=2',1),
(5,'review','New review received!','Rami gave you 5 stars for the tan leather wingback chair.','/src/config/pages/profile.php?id=5',1),
(2,'system','Your listing is trending!','Your Laptop, Smartphone & Tablet bundle has over 400 views this week.','/src/config/pages/listing-detail.php?id=2',0),
(5,'system','Auction ending soon!','Your Porsche 911 Cabriolet auction ends in 4 days, current bid is 24,500 JD.','/src/config/pages/listing-detail.php?id=16',0),
(2,'bid','Your auction has new bids!','The Fine Jewellery Set is attracting a lot of attention.','/src/config/pages/listing-detail.php?id=19',0);

INSERT INTO `swap_offers` (`sender_id`,`receiver_id`,`offered_listing`,`wanted_listing`,`message`,`cash_addition`,`status`) VALUES
(8,3,15,14,'Hi Sara, would you swap your indoor plant for my wooden table easel art set? I think it would look lovely in your room.',0,'pending'),
(3,8,14,15,'Hi Noor, happy to trade my leafy plant for the watercolour easel set for my niece. Could add a small book too.',0,'pending'),
(5,8,9,15,'Hello Noor, any interest in swapping the cream tub chair for your art easel set? I can add some cash on top.',20.00,'accepted');
