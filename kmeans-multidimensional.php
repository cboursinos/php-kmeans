<?php
	$input = array(
				array('user_id' => 1,
					  'residents'=>1,
					  'homeSize'=>100),
				array('user_id' => 2,
					  'residents'=>2,
					  'homeSize'=>120),
				array('user_id' => 3,
					  'residents'=>1,
					  'homeSize'=>130),
				array('user_id' => 4,
					  'residents'=>2,
					  'homeSize'=>140),
				array('user_id' => 5,
					  'residents'=>1,
					  'homeSize'=>100),
				array('user_id' => 6,
					  'residents'=>3,
					  'homeSize'=>200),
				array('user_id' => 7,
					  'residents'=>4,
					  'homeSize'=>210),
				array('user_id' => 8,
					  'residents'=>3,
					  'homeSize'=>220),
				array('user_id' => 9,
					  'residents'=>4,
					  'homeSize'=>200),
				array('user_id' => 10,
					  'residents'=>3,
					  'homeSize'=>210)	
			);

				$k = 2;
				$attribute = array('residents');
				$iterations = 5;
				$clusters = kmeans($input, $k, $attribute,$iterations);
				
			
				$sum_all = 0;
				foreach ($clusters as $value){
					$sum = 0;
					foreach ($value as $table){//$table['cluster_centroid']
						$sum1 = 0;
						for ($i = 0; $i < sizeof($attribute); $i++) {
							$sum1 = $sum1 + pow($table[$attribute[$i]] - $table['cluster_centroid'][$i],2);
						}
						$sum = $sum + $sum1;
					}
					$sum_all = $sum_all + $sum;
				}
				
		while($sum_all >= 30){
				$k = 2;
				$attribute = array('residents');
				$iterations = 5;
				$clusters = kmeans($input, $k, $attribute,$iterations);
				
				$sum_all = 0;
				foreach ($clusters as $value){
					$sum = 0;
					foreach ($value as $table){
						$sum1 = 0;
						for ($i = 0; $i < sizeof($attribute); $i++) {
							$sum1 = $sum1 + pow($table[$attribute[$i]] - $table['cluster_centroid'][$i],2);
						}
						$sum = $sum + $sum1;
					}
					$sum_all = $sum_all + $sum;
				}
			 }			
			
				$min = 0;
				foreach ($clusters as $value){
					foreach ($value as $table){
						for ($i = 0; $i < sizeof($attribute); $i++) {
							$min =  $table['cluster_centroid'][0];
						}
					}
				}
			
				foreach ($clusters as $value){
					foreach ($value as $table){
						for ($i = 0; $i < sizeof($attribute); $i++) {
							if($table['cluster_centroid'][0]<$min){
								$min = $table['cluster_centroid'][0];
							}
						}
					}
				}
			
			foreach ($input as $user){
				foreach ($clusters as $key => $value){
					foreach ($value as $table){
						if($user['user_id'] == $table['user_id']){
							$kwh = $table['cluster_centroid'][0];
							echo 'User_id: '.$table['user_id'].' Cluster: '.$key;
							echo '</br>';	
						}
					}
				}
			}
			
			
	function kmeans(&$input, $k, $attribute ,$iterations){
		if(empty($input)){
			return array();
		}

		#
		# if we're dealing with scalars, then just take them as is; otherwise,
		# extract just the values of interest and put it in a new array
		#
		$values = $attribute ? kmeans_values($input, $attribute) : $input; //exei ginei allagei kai pernaei mesata attributes

		# setup
		$cluster_map = array();
		$centroids = kmeans_initial_centroids($values, $k);//edw exw parei random times mesa apo to values

		#
		# warning: this is recursive...
		#
		
		$clusters = kmeans_cluster($values, $cluster_map, $centroids,$iterations);
		
		//while(sizeof($clusters) != $k){
		//	$clusters = kmeans_cluster($values, $cluster_map, $centroids,$iterations);	
		//}
		return $attribute ? kmeans_rebuild($input, $clusters, $attribute,$centroids) : $clusters;
	}

	#
	# perform the actual clustering
	#
	function kmeans_cluster(&$values, &$cluster_map, &$centroids,$iterations){
		$num_changes = 0;
		
		foreach ($values as $index => $value){//auto einai 15
			$min_distance = null;
			$new_cluster = null;
			foreach ($centroids as $cluster_index => $centroid){
					//edw tha prepei na kanw afairesei twn timwn me tis antistoixes times
					$sum = 0;
					for ($i = 0; $i < sizeof($value); $i++) {
						$sum = $sum + abs($value[$i] - $centroid[$i]); 
					}
					$distance = abs($sum);
					//$distance = abs($value - $centroid);//auto thelei allagei gia na pairnei ta attributes
					if (is_null($min_distance) || $min_distance >= $distance){
						$min_distance = $distance;
						$new_cluster = $cluster_index;
					}
				if (!isset($cluster_map[$index]) || $new_cluster != $cluster_map[$index]){
					$num_changes++;
				}
				$cluster_map[$index] = $new_cluster;
			}
			
		}
		$clusters = kmeans_populate_clusters($values, $cluster_map);//edw den eginan allages
	
		#
		# TODO: we probably want to be able to get out of the clustering
		# sooner, otherwise we may be here all day.
		#
		# perhaps maintain state and keep track of how many iterations we've
		# been through vs how many changes are coming out of each successive iteration.
		# wouldn't want an infinite recursion or anything...
		#
		//if ($num_changes){
			for ($i = 0; $i < $iterations; $i++) {
				$centroids = kmeans_recalculate_centroids($clusters, $centroids);//edw egine allagi kai gurnaei to mean gia kathe ena apo ta stoixeia
				$iterations = $iterations -1;
				kmeans_cluster($values, $cluster_map, $centroids,$iterations);
			}
		//}
		return $clusters;
	}

	#
	# figure out centroids (means) for the clusters as they are
	#
	function kmeans_recalculate_centroids($clusters, $centroids){ //edw eimai...........................................
		
		//var_dump($clusters);
		
		foreach ($clusters as $cluster_index => $cluster){
			$cluster_values = array_values($cluster);
			$count = sizeof($cluster_values);
			$sum = array();
			for ($i = 0; $i < sizeof($cluster_values[0]); $i++) {
				$suma = 0;
				foreach ($cluster_values as $values){
					$suma = $suma + $values[$i];
				} 
				$sum[$i] = $suma/$count;
			}
			//edw prepei na parw to mean twn stoixeiwn pou uparxoun mesa...
			
			$mean = $sum;
			if ($centroids[$cluster_index] != $mean){
				$centroids[$cluster_index] = $mean;
			}
		}
		return $centroids;
	}

	#
	# set up some reasonable defaults for centroid values
	#
	function kmeans_initial_centroids(&$values, $k){//edw twra thelw na parw kentrika simeia osa kai ta k
		$centroids = array();
		#$max = max($values);
		#$min = min($values);
		
		#$interval = ceil(($max-$min) / $k);
		#var_dump($interval);
		$set = $values;
		while (0 <= --$k){
			$cent = array_rand($set);
			for ($i = 0; $i < sizeof($set[$cent]); $i++) {
				$set[$cent][$i] = $set[$cent][$i];///sizeof($set)
			}
			$centroids[$k] = $set[$cent]; 
			unset($set[$cent]);
		}
		return $centroids; // random ap tis times pou peirame apo to values
	}

	#
	# in the event that we're dealing with an array of objects, extract just a
	# key => value of interest mapping first
	#
	function kmeans_values(&$input, $attribute){ //exei mpei edw to for gia na diavazei to posa attributes exw balei mesa kai na krataei auta
		$values = array();		
		
		foreach ($input as $index => $value){
			$value = (array)$value;
			for ($i = 0; $i < sizeof($attribute); $i++) {
					$values[$index][$i] = $value[$attribute[$i]];
			}
		}
		return $values;
	}

	#
	# convert the $index => $cluster_index map to a $cluster_index => $cluster map
	# ($cluster is a $index => $value mapping)
	#
	function kmeans_populate_clusters(&$values, &$cluster_map){
		$clusters = array();
		
		foreach ($cluster_map as $index => $cluster){
			$clusters[$cluster][$index] = $values[$index];
		}
		return $clusters;
	}

	#
	# if we're dealing with non-scalars, re-attach the actual objects to their
	# indexes in the clusters, and populate the objects with useful cluster info
	#
	function kmeans_rebuild(&$input, &$clusters, $attribute,$centroids){
		if ($attribute){
			$clusters_rebuilt = array();
			foreach ($clusters as $cluster_index =>$cluster){
				$cluster_size = count($cluster);
				foreach ($cluster as $index => $value){
					if (is_array($input[$index])){
							$cluster_centroid = "cluster_centroid";
							$input[$index][$cluster_centroid] = $centroids[$cluster_index];
						
					}else{
							$cluster_centroid = "cluster_centroid";
							$input[$index][$cluster_centroid] = $centroids[$cluster_index];
						
					}
					$clusters_rebuilt[$cluster_index][$index] = $input[$index];
				}
			}
		}else{
			$clusters_rebuilt = $clusters;
		}

		return $clusters_rebuilt;
	}
?>