-- El Confidencial - projekt Programiranje web aplikacija
-- Uvoz u phpMyAdmin: Import -> odabrati ovu datoteku -> Go

CREATE DATABASE IF NOT EXISTS el_confidencial
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_croatian_ci;

USE el_confidencial;

DROP TABLE IF EXISTS korisnik;
DROP TABLE IF EXISTS vijesti;

CREATE TABLE vijesti (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  datum DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  naslov VARCHAR(180) NOT NULL,
  sazetak VARCHAR(500) NOT NULL,
  tekst TEXT NOT NULL,
  slika VARCHAR(255) NOT NULL,
  kategorija VARCHAR(32) NOT NULL,
  arhiva TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  INDEX idx_kategorija_arhiva_datum (kategorija, arhiva, datum)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_croatian_ci;

CREATE TABLE korisnik (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  ime VARCHAR(50) NOT NULL,
  prezime VARCHAR(50) NOT NULL,
  korisnicko_ime VARCHAR(32) NOT NULL,
  lozinka VARCHAR(255) NOT NULL,
  razina TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_korisnicko_ime (korisnicko_ime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_croatian_ci;

INSERT INTO vijesti (datum, naslov, sazetak, tekst, slika, kategorija, arhiva) VALUES
('2026-06-18 14:35:00', 'Los ''tories'', en modo Monty Python: el ocaso de los conservadores británicos', 'La política británica atraviesa una etapa de profundos cambios y sus dirigentes buscan una nueva identidad.', 'La política británica vive una transformación acelerada. Tras años de tensiones internas, los conservadores intentan reconstruir un proyecto común y recuperar la confianza de sus votantes.\n\nLos analistas señalan que el cambio generacional será decisivo para definir el futuro del partido y su relación con Europa.', 'seed:may.jpg', 'europa', 0),
('2026-06-17 15:05:00', 'Angela Merkel descarta categóricamente querer entrar en política europea', 'La excanciller rechaza volver a la primera línea política y afirma que su etapa institucional ha terminado.', 'Angela Merkel ha descartado asumir nuevas responsabilidades institucionales. La antigua canciller alemana considera que Europa necesita espacio para una nueva generación de líderes.\n\nEn sus intervenciones públicas insiste en la importancia del diálogo, la estabilidad y la cooperación entre los Estados miembros.', 'seed:merkel.jpg', 'europa', 0),
('2026-06-16 16:20:00', 'Los nuevos líderes de la UE no sufrieron la Europa de ayer', 'Napoleón decía que si querías conocer a alguien tenías que ver cómo era el mundo cuando tenía 20 años. Los hombres y mujeres que van a liderar la UE no vivieron sus crisis fundacionales.', 'Se suele atribuir a Napoleón la frase según la cual para conocer a un hombre -y hoy añadiríamos que a una mujer- hay que conocer cómo era el mundo cuando tenía veinte años. Es algo a tener en cuenta ahora que muchos de quienes ocupan los cargos más altos serán sustituidos por gente de la siguiente generación.\n\nCuando Mario Draghi tenía veinte años, Italia aún estaba marcada por la democracia cristiana surgida tras la Segunda Guerra Mundial. En Luxemburgo, el acero empezaba a dejar de ser el producto básico de la economía y Europa avanzaba hacia una integración más amplia.', 'seed:europa_lideri.jpg', 'europa', 0),
('2026-06-18 14:08:00', 'Científicos descubren un "dramático adelgazamiento" del hielo en la Antártida', 'Nuevas mediciones muestran una pérdida acelerada de espesor en varias plataformas de hielo antárticas.', 'Un equipo internacional ha comparado observaciones de satélite de varias décadas y ha detectado cambios significativos en el espesor del hielo.\n\nLos científicos advierten de que la continuidad de estas tendencias puede influir en el nivel del mar y en la circulación oceánica global.', 'seed:antarktika.jpg', 'teknautas', 0),
('2026-06-17 13:42:00', 'Satélites revelan cambios inesperados en los glaciares', 'Las imágenes de alta resolución permiten seguir el movimiento del hielo con una precisión sin precedentes.', 'Los nuevos sensores orbitales registran pequeñas variaciones en la superficie y velocidad de los glaciares. La información se combina con modelos climáticos para mejorar las predicciones.\n\nEl proyecto publica una parte de los datos para que universidades y centros de investigación puedan analizarlos.', 'seed:antarktika.jpg', 'teknautas', 0),
('2026-06-16 12:30:00', 'La tecnología que vigila el futuro del hielo polar', 'Senzori, sateliti i algoritmi prate promjene ledenog pokrova gotovo u stvarnom vremenu.', 'La vigilancia del hielo polar combina estaciones automáticas, radares, satélites y aprendizaje automático. Estas herramientas ayudan a detectar grietas y desplazamientos antes de que sean visibles desde tierra.\n\nLos investigadores esperan que la tecnología permita responder con mayor rapidez a los cambios ambientales.', 'seed:antarktika.jpg', 'teknautas', 0),
('2026-06-10 09:00:00', 'Primjer arhivirane vijesti', 'Ovaj zapis postoji u bazi, ali se ne prikazuje na javnoj naslovnici.', 'Administrator može promijeniti status arhive i ponovno objaviti ovu vijest.', 'seed:merkel.jpg', 'europa', 1);

-- Demo računi:
-- administrator / admin123 (razina 1)
-- korisnik / korisnik123 (razina 0)
INSERT INTO korisnik (ime, prezime, korisnicko_ime, lozinka, razina) VALUES
('Admin', 'Portal', 'administrator', '$2y$12$DE9TD0OscUc3gO328SDi3uYGncZ4PCHjXi./2RMhf1Ac3JGw3CWZS', 1),
('Obični', 'Korisnik', 'korisnik', '$2y$12$O2T/wXVksHk55xbMQaXsge7hGs0bpAqZl8XdXG8LiE9U4aGy61ypC', 0);
