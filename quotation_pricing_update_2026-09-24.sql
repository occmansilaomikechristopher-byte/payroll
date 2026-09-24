-- Keep ₱300 per sq. ft. only for Product + Glass Thickness.
-- Optional Glass/Profile pricing uses its own configured matrix rate.
-- Clear Glass + Black Aluminum uses the supplied ₱4,500 / 16 sq. ft. rate.

INSERT INTO pos_quotation_pricing
    (width, height, unit, glass_color, thickness, aluminum_profile, price_per_sq_ft, is_active)
VALUES
    (48, 48, 'IN', '', 5, '', 300.0000, 1),
    (48, 48, 'IN', '', 6, '', 300.0000, 1),
        (48, 48, 'IN', 'Clear Glass', 5, 'Black', 281.2500, 1),
        (48, 48, 'IN', 'Clear Glass', 6, 'Black', 281.2500, 1)
ON DUPLICATE KEY UPDATE
    price_per_sq_ft = VALUES(price_per_sq_ft),
    is_active = VALUES(is_active);

UPDATE pos_quotation_pricing
SET is_active = 0
WHERE width = 48 AND height = 48 AND unit = 'IN'
    AND glass_color = 'Clear Glass'
    AND aluminum_profile = 'White'
    AND thickness IN (5, 6);
