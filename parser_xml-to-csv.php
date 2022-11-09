<?php
//youtube.com/SENATOROV

$input = 'input_01.xml';     //XML файл или URL
$output_path = '';   //директория сохранения CSV файла, если ничего не указано - корень.
//создаем в переменной $parse объект по классу XmlToCsv. Т.е. В $parse хранится вся инстукция(значения) класса XmlToCsv
$parse = new XmlToCsv;
$fields = $parse->parsing($input); //кладём в переменную-массив файл xml
$csv = $parse->generate_csv($fields, $output_path); //присваиваем переменной созданный csv файл
//создаем класс, для создания общей инструкции
class XmlToCsv
{
	//объявляем глобальную область видимости для функции
	public function parsing($xml_source)
	{


		//заголовок csv

		//          asin - для сопоставления ключей входного и выходного массива. ASIN - название столбца.
		$fields[0]['name'] = 'name';
		$fields[0]['sku'] = 'sku';
		$fields[0]['description'] = 'description';
		$fields[0]['category1'] = 'category1';
		$fields[0]['category2'] = 'category2';
		$fields[0]['image_url'] = 'image_url';
		$fields[0]['price'] = 'price';
		$fields[0]['brend'] = 'brend';
		//создаем объект SimpleXMLElement который работает по инструкции(модели) XmlToCsv, передаём в него аргумент функции
		$xml = new SimpleXMLElement($xml_source, null, true);
		// парсим все категории и записываем в ассоциативный массив
		$category_arr = array();
		foreach ($xml->shop->categories->category as $category) {
			$id_category = (int)$category["id"];
			$parent_id = (int)$category["parentId"];
			$name_category = $category;
			$category_arr[$id_category] = array('name_category' => $name_category, 'parent_id' => $parent_id);
			echo $id_category, " * ", $name_category, " * ", $parent_id, "<br>";
		}
		//объявляем интерацию с "1" для перехода к след. элем.
		$n = 1;
		//создаем цикл для перебора элементов

		//первый форич для ссылки(верхний уровень), второй опирается на него и нужен для его дочек(нижний уровень,основные данные таблицы)
		foreach ($xml->shop->offers->offer as $item) {
			$name = (string)$item->name;
			$sku = $item['id'];
			echo $sku, " : ", $name, "<br>";
			$description = $item->description;
			$price = $item->price;
			$brend = $item->vendor;
			$category_id = (int)$item->categoryId;
			$category_id_parent = $category_arr[$category_id]['parent_id'];
			$category2 = $category_arr[$category_id]['name_category'];
			$category1 = $category_arr[$category_id_parent]['name_category'];
			echo "***01***", $category_id, " : ", $category2, " : ", $category1, "<br>";
			$image_url = $item->picture[0];
			echo $image_url, "<br>";
			$image_arr = "";
			$i = 0;
			foreach ($item->picture as $picture) {
				$i++;
				if ($i > 1) {
					$image_arr .= "#*#" . $picture;
				} else {
					$image_arr .= $picture;
				}
			}
			echo $image_arr, "<br>";

			//запись в csv
			$fields[$n]['name'] = $name;
			$fields[$n]['sku'] = $sku;
			$fields[$n]['description'] = $description;
			$fields[$n]['category1'] = $category1;
			$fields[$n]['category2'] = $category2;
			$fields[$n]['image_url'] = $image_url;
			$fields[$n]['price'] = $price;
			$fields[$n]['brend'] = $brend;
			$n++; //переход к следующему элименту.

		}

		return $fields;
	}

	public function generate_csv($data, $output)
	{

		//$date = date("Y-m-d"); если требуется дата в имени
		$path = 'output.csv';     //имя файла // генератор имени - $output . $_SERVER['SERVER_NAME'] . '_' . $date . ' .csv';

		$csv = fopen($path, 'w');


		foreach ($data as $field) {
			fputcsv($csv, $field, ';');   //конвертирование файла
		}

		fclose($csv);

		return $csv;
	}
}
