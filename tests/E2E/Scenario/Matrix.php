<?php declare(strict_types=1);
/**
 * End-to-end Matrix
 *
 * Derive project and Docker scenarios from the wizard definitions.
 *
 * PHP version 8.3
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
namespace Tests\E2E\Scenario;

/**
 * Dependencies
 */
use CrazyPHP\Model\Docker\Install;
use CrazyPHP\Model\App\Create;
use InvalidArgumentException;
use CrazyPHP\Cli\Form;

/**
 * Matrix
 *
 * Read the questions of `crazy new project` and `crazy docker new` so that a choice added to
 * either wizard is covered without editing the tests.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2026 Kévin Zarshenas
 */
class Matrix {

    /** Public Constants
     ******************************************************
     */

    /** @var array STRATEGIES Supported selections, smallest first */
    public const STRATEGIES = ["default", "each", "pairwise", "full"];

    /** Public Static Methods
     ******************************************************
     */

    /**
     * Scenarios
     *
     * @param string $strategy One of STRATEGIES
     * @param array $filter Accepted values, keyed by parameter (`type` => ["api"], `server.type` => ["nginx"])
     * @param int|null $limit Maximum number of scenarios
     * @return Scenario[]
     */
    public static function scenarios(string $strategy = "pairwise", array $filter = [], ?int $limit = null):array {

        # Reject unknown strategies instead of silently running everything
        if(!in_array($strategy, self::STRATEGIES, true))

            # Explain the accepted values
            throw new InvalidArgumentException(
                "Unknown e2e strategy \"$strategy\"; use ".implode(", ", self::STRATEGIES)."."
            );

        # Prepare candidates
        $all = [];

        # Iteration of project answers
        foreach(self::branches(Create::getRequiredValues()) as $create){

            # Iteration of Docker answers
            foreach(self::branches(Install::getRequiredValues()) as $docker){

                # New scenario
                $scenario = new Scenario($create, $docker);

                # Keep the combination when it matches the filter, indexed by id to drop duplicates
                if(self::_matches($scenario, $filter))

                    # Add candidate
                    $all[$scenario->id()] ??= $scenario;

            }

        }

        # Keep a stable order
        $all = array_values($all);

        # Select the requested subset
        $result = match($strategy){
            "default"   => array_slice($all, 0, 1),
            "each"      => self::_cover($all, 1),
            "pairwise"  => self::_cover($all, 2),
            "full"      => $all,
        };

        # Return the selection, limited when requested
        return $limit === null
            ? $result
            : array_slice($result, 0, $limit)
        ;

    }

    /**
     * Branches
     *
     * Walk a wizard's questions like `Cli\Form`: hidden questions are skipped, select questions
     * branch on each choice, multiple selects branch on each subset and free-text questions keep
     * no answer. The first branch is always the all-defaults answer.
     *
     * @param array $fields Question definitions
     * @param array $answers Answers collected so far
     * @return array[]
     */
    public static function branches(array $fields, array $answers = []):array {

        # Check every question is answered
        if(empty($fields))

            # Return the completed answer set
            return [$answers];

        # Take the next question
        $field = array_shift($fields);

        # Follow the form's visibility rules
        if(!Form::isVisible($field, $answers))

            # Continue without answering the hidden question
            return self::branches($fields, $answers);

        # Prepare result
        $result = [];

        # Iteration of the possible answers, default first
        foreach(self::_answers($field, $answers) as $value)

            # Continue the walk with this answer
            array_push($result, ...self::branches($fields, [$field["name"] => $value] + $answers));

        # Return result
        return $result;

    }

    /** Private Static Methods
     ******************************************************
     */

    /**
     * Answers
     *
     * @param array $field Question definition
     * @param array $answers Previous answers
     * @return array
     */
    private static function _answers(array $field, array $answers):array {

        # Free-text questions (name, ports...) do not branch; the scenario fills them per run
        if(!isset($field["select"]))

            # Single unanswered branch, without calling default providers such as port probes
            return [null];

        # Read the static default used to order choices
        $default = $field["default"] ?? null;

        # Read the choices offered after the previous answers
        $choices = array_map("strval", array_keys(Form::getChoices($field, $answers)));

        # Check multiple choices
        if($field["multiple"] ?? false){

            # Start with the empty subset
            $subsets = [[]];

            # Iteration of choices
            foreach($choices as $choice)

                # Iteration of existing subsets
                foreach($subsets as $subset)

                    # Add the choice, keeping subsets in choice order
                    $subsets[] = [...$subset, $choice];

            # Return subsets
            return $subsets;

        }

        # Put the default first so that branch 0 is the default project
        usort($choices, static fn($a, $b) => ($b === (string) $default) <=> ($a === (string) $default));

        # Return choices
        return $choices;

    }

    /**
     * Matches
     *
     * @param Scenario $scenario Candidate
     * @param array $filter Accepted values per parameter
     * @return bool
     */
    private static function _matches(Scenario $scenario, array $filter):bool {

        # Read the candidate's flat parameters
        $parameters = $scenario->parameters();

        # Iteration of filters
        foreach($filter as $name => $accepted)

            # Reject candidates with another value
            if(!in_array($parameters[$name] ?? null, (array) $accepted, true))

                # Filtered out
                return false;

        # Keep the candidate
        return true;

    }

    /**
     * Cover
     *
     * Greedy t-wise covering: keep picking the candidate that covers the most value combinations
     * not covered yet. With t = 2 every pair of choices (e.g. PostgreSQL with FrankenPHP, Webpack with
     * PHP 8.3) appears in at least one scenario, using a few dozen scenarios instead of thousands.
     *
     * @param Scenario[] $all Candidates, the default first
     * @param int $strength 1 = every value once, 2 = every pair of values
     * @return Scenario[]
     */
    private static function _cover(array $all, int $strength):array {

        # Check there is something to cover
        if(empty($all))

            # Return empty selection
            return [];

        # Prepare indexes; combinations are stored as integers to keep thousands of candidates in memory
        $dictionary = [];
        $tuples = [];
        $uncovered = [];

        # Iteration of candidates
        foreach($all as $index => $scenario){

            # Iteration of the candidate's combinations
            foreach(self::_tuples($scenario->coverage(), $strength) as $tuple){

                # Get the combination's integer id
                $id = $dictionary[$tuple] ??= count($dictionary);

                # Index it for the candidate
                $tuples[$index][] = $id;

                # Mark it as uncovered
                $uncovered[$id] = true;

            }

        }

        # Always start with the default project
        $selected = [0 => $all[0]];

        # Iteration of its combinations
        foreach($tuples[0] as $tuple)

            # Mark as covered
            unset($uncovered[$tuple]);

        # Pick until everything reachable is covered
        while(!empty($uncovered)){

            # Prepare the best candidate
            $best = null;
            $bestScore = 0;

            # Iteration of candidates
            foreach($tuples as $index => $candidate){

                # Skip selected candidates
                if(isset($selected[$index]))

                    # Next candidate
                    continue;

                # Count new combinations
                $score = 0;

                # Iteration of the candidate's combinations
                foreach($candidate as $tuple)

                    # Check combination is still uncovered
                    if(isset($uncovered[$tuple]))

                        # Increment score
                        $score++;

                # Keep the first best for a stable order
                if($score > $bestScore){

                    # Set best candidate
                    $best = $index;
                    $bestScore = $score;

                }

            }

            # Stop if nothing improves coverage
            if($best === null)

                # Stop selection
                break;

            # Select the best candidate
            $selected[$best] = $all[$best];

            # Iteration of its combinations
            foreach($tuples[$best] as $tuple)

                # Mark as covered
                unset($uncovered[$tuple]);

        }

        # Return selection in pick order
        return array_values($selected);

    }

    /**
     * Tuples
     *
     * @param array $parameters Flat parameters
     * @param int $strength Tuple size (1 or 2)
     * @return string[]
     */
    private static function _tuples(array $parameters, int $strength):array {

        # Prepare values
        $values = [];

        # Iteration of parameters
        foreach($parameters as $name => $value)

            # Encode the parameter value
            $values[] = "$name=$value";

        # Check single values are requested
        if($strength === 1)

            # Return values
            return $values;

        # Prepare result
        $result = [];
        $count = count($values);

        # Iteration of values
        for($i = 0; $i < $count; $i++)

            # Iteration of the following values
            for($j = $i + 1; $j < $count; $j++)

                # Add the pair
                $result[] = $values[$i]."&".$values[$j];

        # Return result
        return $result;

    }

}
