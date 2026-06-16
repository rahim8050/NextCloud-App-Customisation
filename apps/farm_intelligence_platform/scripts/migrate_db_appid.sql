-- Migration: rename appid from weather_apis to farm_intelligence_platform
-- Run after the app directory has been renamed and Nextcloud has been
-- put into maintenance mode.

UPDATE oc_appconfig SET appid = 'farm_intelligence_platform' WHERE appid = 'weather_apis';
UPDATE oc_migrations SET app = 'farm_intelligence_platform' WHERE app = 'weather_apis';
UPDATE oc_jobs SET class = REPLACE(class, 'OCA\\WeatherApis\\', 'OCA\\FarmIntelligencePlatform\\') WHERE class LIKE '%OCA\\WeatherApis\\%';
UPDATE oc_preferences SET appid = 'farm_intelligence_platform' WHERE appid = 'weather_apis';
