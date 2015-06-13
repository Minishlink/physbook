<?php
// de http://w-shadow.com/blog/2008/12/10/fast-weighted-random-choice-in-php/
namespace PJM\AppBundle\Services;

class Random
{
    /**
     * weightedRandom()
     * Randomly select one of the elements based on their weights. Optimized for a large number of elements.
     *
     * @param array $values Array of elements to choose from
     * @param array $weights An array of weights. Weight must be a positive number.
     * @param array $lookup Sorted lookup array
     * @param int $total_weight Sum of all weights
     * @return mixed Selected element
     */
    public function weightedRandom($values, $weights, $lookup = null, $total_weight = null)
    {
        if ($lookup == null) {
            list($lookup, $total_weight) = $this->calcLookups($weights);
        }

        $r = mt_rand(0, $total_weight);
        return $values[$this->binarySearch($r, $lookup)];
    }

    /**
     * calcLookups()
     * Build the lookup array to use with binary search
     *
     * @param array $weights
     * @return array The lookup array and the sum of all weights
     */
    public function calcLookups($weights)
    {
        $lookup = array();
        $total_weight = 0;
        $nb_weights = count($weights);

        for ($i=0; $i<$nb_weights; $i++){
            $total_weight += $weights[$i];
            $lookup[$i] = $total_weight;
        }
        return array($lookup, $total_weight);
    }

    /**
     * binarySearch()
     * Search a sorted array for a number. Returns the item's index if found. Otherwise
     * returns the position where it should be inserted, or count($haystack)-1 if the
     * $needle is higher than every element in the array.
     *
     * @param int $needle
     * @param array $haystack
     * @return int
     */
    public function binarySearch($needle, $haystack)
    {
        $high = count($haystack)-1;
        $low = 0;

        while ( $low < $high ){
        $probe = (int)(($high + $low) / 2);
        if ($haystack[$probe] < $needle){
                    $low = $probe + 1;
        } else if ($haystack[$probe] > $needle) {
            $high = $probe - 1;
        } else {
            return $probe;
        }
        }

        if ( $low != $high ){
            return $probe;
        } else {
        if ($haystack[$low] >= $needle) {
            return $low;
        } else {
            return $low+1;
        }
        }
    }
}
