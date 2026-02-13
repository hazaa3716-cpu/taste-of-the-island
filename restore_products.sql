-- Restore Products to food_ordering_db

USE food_ordering_db;

-- Foods (Category ID 1 in old DB -> 'Foods')
INSERT INTO products (name, price, category, image_url, description) VALUES 
('Ugali', 3000, 'Foods', 'https://i.pinimg.com/1200x/fa/8e/03/fa8e03bad48021d28a3181e5e2e4afb0.jpg', 'Traditional cornmeal staple'),
('Pilau', 5000, 'Foods', 'https://i.pinimg.com/736x/29/90/5b/29905b5e366cf1301132177b032beebc.jpg', 'Spiced rice with meat'),
('Samosa', 500, 'Foods', 'https://i.pinimg.com/736x/a8/0a/c5/a80ac5a1b93b074abdf13d64b8cb7acd.jpg', 'Fried pastry with savory filling'),
('Wali Samaki', 5000, 'Foods', 'https://i.pinimg.com/1200x/bf/8f/6e/bf8f6e33c977e66775c3c919ad54b5b7.jpg', 'Rice with fish'),
('Wali wa Nazi', 3000, 'Foods', 'https://i.pinimg.com/736x/01/6f/e9/016fe95aac7d5cef1260569c85112572.jpg', 'Coconut rice'),
('Mahamri', 500, 'Foods', 'https://i.pinimg.com/736x/0a/bf/7f/0abf7fc2520602ec4f97f39e22cd8534.jpg', 'Sweet fried dough'),
('Mishkaki', 1000, 'Foods', 'https://i.pinimg.com/1200x/ad/6c/50/ad6c5089311d053d137e42c61b382665.jpg', 'Marinated meat skewers'),
('Ndizi za kuKaanga', 500, 'Foods', 'https://i.pinimg.com/1200x/24/d0/b9/24d0b9bb0930b2ddae246b46fdc07d0e.jpg', 'Fried plantains'),
('Kuku', 6000, 'Foods', 'https://i.pinimg.com/1200x/1f/10/b3/1f10b3adb83e9ecdd56d2a6d1d430e9b.jpg', 'Fried or roasted chicken'),
('Chipsi', 3000, 'Foods', 'https://i.pinimg.com/736x/3c/54/75/3c54751cdaf44bc2f20a36ece8c4860a.jpg', 'French fries');

-- Drinks (Category ID 2 in old DB -> 'Drinks')
INSERT INTO products (name, price, category, image_url, description) VALUES 
('Chai ya Tangawizi', 500, 'Drinks', 'https://i.pinimg.com/736x/91/e4/3b/91e43b0370c301a6630bf9557fb08a25.jpg', 'Ginger tea'),
('Maji', 500, 'Drinks', 'https://i.pinimg.com/1200x/54/0f/16/540f1669b741ed4ffd0adc0e6583945c.jpg', 'Mineral water'),
('Kahawa', 500, 'Drinks', 'https://i.pinimg.com/736x/f8/38/17/f83817560f3c6cc3c9b311ad82517daf.jpg', 'Coffee'),
('Juice ya Matunda', 500, 'Drinks', 'https://i.pinimg.com/736x/b3/3c/24/b33c24ddd7c4fe45f4bb6f71d37e32e1.jpg', 'Fresh fruit juice'),
('Soda', 600, 'Drinks', 'https://i.pinimg.com/1200x/79/6c/34/796c34906c9111f02f41a319298a261b.jpg', 'Soda'),
('Maziwa Freshi', 1000, 'Drinks', 'https://i.pinimg.com/736x/4e/e9/2b/4ee92bb7febf5a17825e77cd2cba416d.jpg', 'Fresh milk'),
('Milk shake', 2000, 'Drinks', 'https://i.pinimg.com/736x/ba/c9/0d/bac90dcb464636dbad025d79c19a93e4.jpg', 'Milkshake'),
('Maziwa mgando', 1000, 'Drinks', 'https://i.pinimg.com/1200x/e3/0e/0a/e30e0a3070bae2b2ec7a9a6101c85b7b.jpg', 'Yoghurt');
