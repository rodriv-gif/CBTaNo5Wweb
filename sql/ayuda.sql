-- Tabla de los temas de la pantalla de Ayuda.
-- Ejecútalo una sola vez en phpMyAdmin (pestaña SQL) sobre la base u408544566_biblioCBTa5.

CREATE TABLE IF NOT EXISTS tblayuda (
    id_ayuda INT UNSIGNED NOT NULL AUTO_INCREMENT,
    pregunta VARCHAR(200) NOT NULL,
    respuesta TEXT NOT NULL,
    orden INT NOT NULL DEFAULT 0,
    fecha_alta DATETIME NOT NULL,
    id_bibliotecario INT NULL,
    PRIMARY KEY (id_ayuda)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Temas iniciales (los mismos de la maqueta). Después el bibliotecario agrega o elimina desde ayuda_admin.php
INSERT INTO tblayuda (pregunta, respuesta, orden, fecha_alta) VALUES
    ('¿Cómo reservo un libro?', 'Las reservas se hacen desde la app móvil con la misma cuenta que creaste en esta página. En la web puedes consultar el catálogo y revisar tus reservas.', 1, NOW()),
    ('¿Cómo cancelo una reserva?', 'Entra a Mi cuenta, abre Mis reservas y presiona «Cancelar reserva».', 2, NOW()),
    ('¿Dónde descargo mi comprobante?', 'En Mi cuenta, dentro de Mis reservas, presiona «Descargar comprobante». Preséntalo al recoger tu libro.', 3, NOW()),
    ('¿Cómo cambio mi contraseña?', 'En Mi cuenta presiona «Cambiar contraseña», escribe tu contraseña actual y elige una nueva de mínimo 8 caracteres.', 4, NOW()),
    ('¿Cómo pago una multa?', 'Los adeudos se liquidan de forma presencial en la biblioteca.', 5, NOW());
