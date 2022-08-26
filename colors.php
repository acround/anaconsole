<?php

/*
  \033[0m		все атрибуты по умолчанию
  \033[1m		жирный шрифт (интенсивный цвет)
  \033[2m		полу яркий цвет (тёмно-серый, независимо от цвета)
  \033[4m		подчеркивание
  \033[5m		мигающий
  \033[7m		реверсия (знаки приобретают цвет фона, а фон -- цвет знаков)

  \033[22m	установить нормальную интенсивность
  \033[24m	отменить подчеркивание
  \033[25m	отменить мигание
  \033[27m	отменить реверсию

  \033[30m	чёрный цвет знаков
  \033[31m	красный цвет знаков
  \033[32m	зелёный цвет знаков
  \033[33m	желтый цвет знаков
  \033[34m	синий цвет знаков
  \033[35m	фиолетовый цвет знаков
  \033[36m	цвет морской волны знаков
  \033[37m	серый цвет знаков

  \033[40m	чёрный цвет фона
  \033[41m	красный цвет фона
  \033[42m	зелёный цвет фона
  \033[43m	желтый цвет фона
  \033[44m	синий цвет фона
  \033[45m	фиолетовый цвет фона
  \033[46m	цвет морской волны фона
  \033[47m	серый цвет фона
 *
 *
  Таблица цветов и фонов:

  Цвет			код			код фона

  black	30	40	\033[30m	\033[40m
  red		31	41	\033[31m	\033[41m
  green	32	42	\033[32m	\033[42m
  yellow	33	43	\033[33m	\033[43m
  blue	34	44	\033[34m	\033[44m
  magenta	35	45	\033[35m	\033[45m
  cyan	36	46	\033[36m	\033[46m
  grey	37	47	\033[37m	\033[47m
 */

define('COLOR_RED', "\033[31m");
define('COLOR_GREEN', "\033[32m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_DEF', "\033[0m");
echo COLOR_RED . 'COLOR_RED' . COLOR_DEF . "\n";
echo COLOR_GREEN . 'COLOR_GREEN' . COLOR_DEF . "\n";
echo COLOR_YELLOW . 'COLOR_YELLOW' . COLOR_DEF . "\n";
echo "\n\n\n";
echo "\033[31m" . 'COLOR_31' . COLOR_DEF . "\n";
echo "\033[32m" . 'COLOR_32' . COLOR_DEF . "\n";
echo "\033[33m" . 'COLOR_33' . COLOR_DEF . "\n";
echo "\033[35m" . 'COLOR_35' . COLOR_DEF . "\n";
echo "\033[36m" . 'COLOR_36' . COLOR_DEF . "\n";
echo "\033[37m" . 'COLOR_37' . COLOR_DEF . "\n";
echo "\033[38m" . 'COLOR_38' . COLOR_DEF . "\n";
echo "\n\n\n";
echo "\033[40m" . 'COLOR_40' . COLOR_DEF . "\n";
echo "\033[41m" . 'COLOR_41' . COLOR_DEF . "\n";
echo "\033[42m" . 'COLOR_42' . COLOR_DEF . "\n";
echo "\033[43m" . 'COLOR_43' . COLOR_DEF . "\n";
echo "\033[44m" . 'COLOR_44' . COLOR_DEF . "\n";
echo "\033[45m" . 'COLOR_45' . COLOR_DEF . "\n";
echo "\033[46m" . 'COLOR_46' . COLOR_DEF . "\n";
echo "\033[47m" . 'COLOR_47' . COLOR_DEF . "\n";
echo "\033[48m" . 'COLOR_48' . COLOR_DEF . "\n";
echo "\033[49m" . 'COLOR_49' . COLOR_DEF . "\n";
echo "\033[50m" . 'COLOR_50' . COLOR_DEF . "\n";
echo "\n\n\n";
echo "\033[1;33;41m" . " Внимание " . COLOR_DEF . "\n\n";
