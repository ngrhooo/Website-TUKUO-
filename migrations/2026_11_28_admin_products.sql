ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'customer';

CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  category ENUM('Pakaian','Sepatu') NOT NULL,
  size VARCHAR(40) NOT NULL,
  condition_label VARCHAR(80) NOT NULL,
  description TEXT NOT NULL,
  price DECIMAL(12,2) NOT NULL,
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  image VARCHAR(500) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO products (id,name,category,size,condition_label,description,price,stock,image) VALUES
(1,'Oversized Shirt','Pakaian','M / L','Sangat baik','Kemeja oversized dengan potongan santai yang mudah dipadukan untuk gaya sehari-hari.',85000,1,'https://raw.githubusercontent.com/ngrhooo/Website-TUKUO-/main/aset_foto/OVS%20SI%20SIZE%2010USED%20VVGC(resmi%20store)100%25%20ORIGINAL%20(AUTHENTIC)PRICE-SOLD%20OUT%20BY%20CODMORE%20INFO%20%26%20D.webp'),
(2,'Classic Sneakers','Sepatu','42','Sangat baik','Sneakers bergaya klasik yang cocok untuk melengkapi outfit kasual.',175000,1,'https://raw.githubusercontent.com/ngrhooo/Website-TUKUO-/main/aset_foto/ADIDAS%20SAMBA%20OG%20J%20WHITE%20PINKSIZE%2038BNIBWTFULLSET%20(resmi%20store)%5BBOX%20OG%20%2B%20TAG%20%2B%20SELIMUT%20%5D100%25%20ORI.webp'),
(3,'Minimal Jacket','Pakaian','L','Baik','Jaket dengan desain minimalis untuk tampilan kasual dan praktis.',135000,1,'https://raw.githubusercontent.com/ngrhooo/Website-TUKUO-/main/aset_foto/HOODIE%20SI%20SIZE%20XLUSED%20VVGC(resmi%20store)100%25%20ORIGINAL%20(AUTHENTIC)PRICE-2.XXX.XXXMORE%20INFO%20%26%20DETA.webp'),
(4,'Everyday Trainers','Sepatu','40','Sangat baik','Sepatu trainer nyaman untuk aktivitas dan penampilan sehari-hari.',160000,1,'https://raw.githubusercontent.com/ngrhooo/Website-TUKUO-/main/aset_foto/ADIDAS%20SPZL%20BLACK%20PINKSIZE%2040USED%20VVGC(resmi%20store)%5BKELENGKAPAN%20SEPATU%20AJA%5D100%25%20ORIGINAL%20(AUTHE.webp'),
(5,'Daily Knitwear','Pakaian','M','Baik','Atasan rajut yang memberi tampilan santai dan mudah dipadukan.',95000,1,'https://raw.githubusercontent.com/ngrhooo/Website-TUKUO-/main/aset_foto/CREWNECK%20SI%20SIZE%2012USED%20VVGC(resmi%20store)100%25%20ORIGINAL%20(AUTHENTIC)PRICE-1.XXX.XXXMORE%20INFO%20%26%20DE.webp'),
(6,'Retro Street Shoes','Sepatu','41','Sangat baik','Sepatu bergaya retro untuk sentuhan streetwear pada outfit kamu.',190000,1,'https://raw.githubusercontent.com/ngrhooo/Website-TUKUO-/main/aset_foto/ADIDAS%20SPZL%20ORENSIZE%2042USED%20VVGCFULLSET%20(resmi%20store)%5BBOX%20OG%20%2B%20TAG%20%2B%20SELIMUT%20%5D100%25%20ORIGINAL%20(AU.webp');