<?php
 
$data = array(1, 3, 2, 5, 6, 2, 3, 1, 30, 36, 45, 3, 15, 17);
 
print_r(kmeans($data, 3));
 
/**
* This function takes a array of integers and the number of clusters to create.
* It returns a multidimensional array containing the original data organized
* in clusters.
*
* @param array $data
* @param int $k
*
* @return array
*/
function kmeans($data, $k)
{
        $cPositions = assign_initial_positions($data, $k);
        $clusters = array();
 
        while(true)
        {
                $changes = kmeans_clustering($data, $cPositions, $clusters);
                if(!$changes)
                {
                        return kmeans_get_cluster_values($clusters, $data);
                }
                $cPositions = kmeans_recalculate_cpositions($cPositions, $data, $clusters);
        }
}
 
/**
*
*/
function kmeans_clustering($data, $cPositions, &$clusters)
{
        $nChanges = 0;
        foreach($data as $dataKey => $value)
        {
                $minDistance = null;
                $cluster = null;
                foreach($cPositions as $k => $position)
                {
                        $distance = distance($value, $position);
                        if(is_null($minDistance) || $minDistance > $distance)
                        {
                                $minDistance = $distance;
                                $cluster = $k;
                        }
                }
                if(!isset($clusters[$dataKey]) || $clusters[$dataKey] != $cluster)
                {
                        $nChanges++;
                }
                $clusters[$dataKey] = $cluster;
        }
 
        return $nChanges;
}
 
 
 
 
function kmeans_recalculate_cpositions($cPositions, $data, $clusters)
{
        $kValues = kmeans_get_cluster_values($clusters, $data);
        foreach($cPositions as $k => $position)
        {
                $cPositions[$k] = empty($kValues[$k]) ? 0 : kmeans_avg($kValues[$k]);
        }
        return $cPositions;
}
 
function kmeans_get_cluster_values($clusters, $data)
{
        $values = array();
        foreach($clusters as $dataKey => $cluster)
        {
                $values[$cluster][] = $data[$dataKey];
        }
        return $values;
}
 
 
function kmeans_avg($values)
{
        $n = count($values);
        $sum = array_sum($values);
        return ($n == 0) ? 0 : $sum / $n;
}
 
/**
* Calculates the distance (or similarity) between two values. The closer
* the return value is to ZERO, the more similar the two values are.
*
* @param int $v1
* @param int $v2
*
* @return int
*/
function distance($v1, $v2)
{
  return abs($v1-$v2);
}
 
/**
* Creates the initial positions for the given
* number of clusters and data.
* @param array $data
* @param int $k
*
* @return array
*/
function assign_initial_positions($data, $k)
{
        $min = min($data);
        $max = max($data);
        $int = ceil(abs($max - $min) / $k);
        while($k-- > 0)
        {
                $cPositions[$k] = $min + $int * $k;
        }
        return $cPositions;
}

?>