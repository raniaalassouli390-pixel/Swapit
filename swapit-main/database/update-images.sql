
TRUNCATE TABLE `listing_images`;

-- Primary image of each listing is the exact photo of that product.
INSERT INTO `listing_images` (`listing_id`,`path`,`is_primary`,`sort_order`) VALUES
(1,  '/public/icons/apple_ww.png',         1, 0),
(2,  '/public/icons/Electronic10.png',     1, 0),
(3,  '/public/icons/Gaming.png',           1, 0),
(4,  '/public/icons/camera1.jpeg',         1, 0),
(5,  '/public/icons/sport.png',            1, 0),
(6,  '/public/icons/clothing10.png',       1, 0),
(7,  '/public/icons/book10.png',           1, 0),
(8,  '/public/icons/Furniture.jpeg',       1, 0),
(9,  '/public/icons/furniture10.png',      1, 0),
(10, '/public/icons/Kitchen4.jpeg',        1, 0),
(11, '/public/icons/Electronic3.jpeg',     1, 0),
(12, '/public/icons/guitar1.png',          1, 0),
(13, '/public/icons/Clothing2.jpeg',       1, 0),
(14, '/public/icons/Garden1.png',          1, 0),
(15, '/public/icons/art1.png',             1, 0),
(16, '/public/icons/Vehicles10.png',       1, 0),
(17, '/public/icons/clook5.png',           1, 0),
(18, '/public/icons/Clook3.jpeg',          1, 0),
(19, '/public/icons/jewelry.png',          1, 0),
(20, '/public/icons/science&habby1.png',   1, 0),
(21, '/public/icons/bage1.png',            1, 0),
(22, '/public/icons/kids.png',             1, 0);
