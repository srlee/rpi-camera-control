<?php
#
#  Raspberry Pi Camera Control Software
#  Copyright (C) 2014 Cansel Software Limited
#
#   This program is free software: you can redistribute it and/or modify
#   it under the terms of the GNU General Public License as published by
#   the Free Software Foundation, either version 3 of the License, or
#    any later version.
#
#   This program is distributed in the hope that it will be useful,
#   but WITHOUT ANY WARRANTY; without even the implied warranty of
#   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#   GNU General Public License for more details.
#
#   You should have received a copy of the GNU General Public License
#   along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#
#
namespace lib;

class DHT22 {
	// Get room temp and humidity from dht22 sensor
    public static function temperature() {
        return (exec("sudo dht22 | grep 'Temp' | cut -d' ' -f3"));
    }

    public static function humidity() {
        return (exec("sudo dht22 | grep 'Humid' | cut -d' ' -f3"));
    }

}

?>




