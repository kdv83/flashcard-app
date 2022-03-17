<?php

namespace Tests\Feature;

use App\Models\Flashcard;
use App\Models\FlashcardPractice;
use App\Models\Practice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Tests\TestCase;

class InteractiveCommandTest extends TestCase
{
    use RefreshDatabase;

    private $main_menu = [
        1 => '1. Create a flashcard',
        2 => '2. List all flashcards',
        3 => '3. Practice',
        4 => '4. Stats',
        5 => '5. Reset',
        6 => '6. Exit',
    ];

    /**
     * Main menu test.
     *
     * @return void
     */
    public function test_main_menu()
    {
        $this->artisan('flashcard:interactive')
            ->expectsChoice('Main menu', 0, $this->main_menu)
            ->assertNotExitCode(1)
        ;
    }

    /**
     * Create flashcard test.
     *
     * @return void
     */
    public function test_create_flashcard()
    {
        $this->artisan('flashcard:interactive')
            ->expectsChoice('Main menu', $this->main_menu[1], $this->main_menu)
            ->expectsQuestion('Enter a question', 'What is your name?')
            ->expectsQuestion('Enter an answer', 'Interactive test')
            ->expectsOutput('Flashcard successfully stored!')
            ->expectsChoice('Main menu', 0, $this->main_menu)
            ->assertNotExitCode(1)
        ;
    }

    /**
     *
     * @return void
     */
    public function test_list_of_flashcards()
    {
        $this->artisan('flashcard:interactive')
            ->expectsChoice('Main menu', $this->main_menu[2], $this->main_menu)
            ->expectsTable(['Question', 'Answer'], Flashcard::all(['question', 'answer'])->toArray())
            ->expectsChoice('Main menu', 0, $this->main_menu)
            ->assertNotExitCode(1)
        ;
    }

    /**
     *
     * @return void
     */
    public function test_practice_coorect_answer()
    {
        $this->createFlashcards();
        $flashcards = Flashcard::all();
        $practice = Practice::firstOrCreate();

        foreach ($flashcards as $flashcard) {
            if(empty($practice->practice_flashcards()->where('flashcard_id', $flashcard->id)->first())) {
                $practice->practice_flashcards()->create([
                    'flashcard_id' => $flashcard->id
                ]);
            }
        }

        $correctly_answered = $practice
            ->practice_flashcards()
            ->where('status', Flashcard::STATUSES[0])->count();

        $percent = round($correctly_answered / $flashcards->count(), 2) * 100;

        $practice_flashcards = $practice
            ->practice_flashcards
            ->map(function ($item, $key) {
                return [
                    'question' => $item->flashcard->id . '. ' . $item->question,
                    'status' => $item->status,
                ];
            })
            ->toArray();
        $separator = new TableSeparator();
        $practice_flashcards[] = $separator;
        $practice_flashcards[] = [new TableCell('% of completion ' . $percent, ['colspan' => 2])];

        $this->artisan('flashcard:interactive')
            ->expectsChoice('Main menu', $this->main_menu[3], $this->main_menu)
            ->expectsTable(['Question', 'Status'], $practice_flashcards)
            ->expectsQuestion('Choose a question', '1')
            ->expectsQuestion('Enter your answer', '21')
            ->expectsOutput('Correct')
            ->expectsChoice('Main menu', 0, $this->main_menu)
            ->assertNotExitCode(1)
        ;
    }

    /**
     *
     * @return void
     */
    public function test_practice_incoorect_answer()
    {
        $this->createFlashcards();
        $flashcards = Flashcard::all();
        $practice = Practice::firstOrCreate();

        foreach ($flashcards as $flashcard) {
            if(empty($practice->practice_flashcards()->where('flashcard_id', $flashcard->id)->first())) {
                $practice->practice_flashcards()->create([
                    'flashcard_id' => $flashcard->id
                ]);
            }
        }

        $correctly_answered = $practice
            ->practice_flashcards()
            ->where('status', Flashcard::STATUSES[0])->count();

        $percent = round($correctly_answered / $flashcards->count(), 2) * 100;

        $practice_flashcards = $practice
            ->practice_flashcards
            ->map(function ($item, $key) {
                return [
                    'question' => $item->flashcard->id . '. ' . $item->question,
                    'status' => $item->status,
                ];
            })
            ->toArray();
        $separator = new TableSeparator();
        $practice_flashcards[] = $separator;
        $practice_flashcards[] = [new TableCell('% of completion ' . $percent, ['colspan' => 2])];

        $this->artisan('flashcard:interactive')
            ->expectsChoice('Main menu', $this->main_menu[3], $this->main_menu)
            ->expectsTable(['Question', 'Status'], $practice_flashcards)
            ->expectsQuestion('Choose a question', '2')
            ->expectsQuestion('Enter your answer', 'Name')
            ->expectsOutput('Incorrect')
            ->expectsChoice('Main menu', 0, $this->main_menu)
            ->assertNotExitCode(1)
        ;
    }

    /**
     *
     * @return void
     */
    public function test_empty_stats()
    {
        $this->artisan('flashcard:interactive')
            ->expectsChoice('Main menu', $this->main_menu[4], $this->main_menu)
            ->expectsOutput('No practice, no stats!')
            ->expectsChoice('Main menu', 0, $this->main_menu)
            ->assertNotExitCode(1)
        ;
    }

    /**
     *
     * @return void
     */
    public function test_stats()
    {
        $this->createFlashcards();
        $flashcards = Flashcard::all();
        $practice = Practice::firstOrCreate();

        foreach ($flashcards as $flashcard) {
            if(empty($practice->practice_flashcards()->where('flashcard_id', $flashcard->id)->first())) {
                $practice->practice_flashcards()->create([
                    'flashcard_id' => $flashcard->id
                ]);
            }
        }

        $correctly_answered = $practice
            ->practice_flashcards()
            ->where('status', Flashcard::STATUSES[0])->count();

        $percent = round($correctly_answered / $flashcards->count(), 2) * 100;

        $practice_flashcards = $practice
            ->practice_flashcards
            ->map(function ($item, $key) {
                return [
                    'question' => $item->flashcard->id . '. ' . $item->question,
                    'status' => $item->status,
                ];
            })
            ->toArray();
        $separator = new TableSeparator();
        $practice_flashcards[] = $separator;
        $practice_flashcards[] = [new TableCell('% of completion ' . $percent, ['colspan' => 2])];

        $this->artisan('flashcard:interactive')
            ->expectsChoice('Main menu', $this->main_menu[3], $this->main_menu)
            ->expectsTable(['Question', 'Status'], $practice_flashcards)
            ->expectsQuestion('Choose a question', '1')
            ->expectsQuestion('Enter your answer', '21')
            ->expectsOutput('Correct')
            ->expectsChoice('Main menu', $this->main_menu[4], $this->main_menu)
            ->expectsTable(['Total', 'Answered %', 'Correctly answered %'], [[2, '50%', '50%']])
            ->expectsChoice('Main menu', 0, $this->main_menu)
            ->assertNotExitCode(1)
        ;
    }

    /**
     *
     * @return void
     */
    public function test_reset()
    {
        $this->createFlashcards();
        $flashcards = Flashcard::all();
        $practice = Practice::firstOrCreate();

        foreach ($flashcards as $flashcard) {
            if(empty($practice->practice_flashcards()->where('flashcard_id', $flashcard->id)->first())) {
                $practice->practice_flashcards()->create([
                    'flashcard_id' => $flashcard->id
                ]);
            }
        }

        $correctly_answered = $practice
            ->practice_flashcards()
            ->where('status', Flashcard::STATUSES[0])->count();

        $percent = round($correctly_answered / $flashcards->count(), 2) * 100;

        $practice_flashcards = $practice
            ->practice_flashcards
            ->map(function ($item, $key) {
                return [
                    'question' => $item->flashcard->id . '. ' . $item->question,
                    'status' => $item->status,
                ];
            })
            ->toArray();
        $separator = new TableSeparator();
        $practice_flashcards[] = $separator;
        $practice_flashcards[] = [new TableCell('% of completion ' . $percent, ['colspan' => 2])];

        $this->artisan('flashcard:interactive')
            ->expectsChoice('Main menu', $this->main_menu[3], $this->main_menu)
            ->expectsTable(['Question', 'Status'], $practice_flashcards)
            ->expectsQuestion('Choose a question', '1')
            ->expectsQuestion('Enter your answer', '21')
            ->expectsOutput('Correct')
            ->expectsChoice('Main menu', $this->main_menu[4], $this->main_menu)
            ->expectsTable(['Total', 'Answered %', 'Correctly answered %'], [[2, '50%', '50%']])
            ->expectsChoice('Main menu', $this->main_menu[5], $this->main_menu)
            ->expectsChoice('Main menu', $this->main_menu[4], $this->main_menu)
            ->expectsOutput('No practice, no stats!')
            ->expectsChoice('Main menu', 0, $this->main_menu)
            ->assertNotExitCode(1)
        ;
    }

    /**
     * Exit test.
     *
     * @return void
     */
    public function test_exit()
    {
        $this->artisan('flashcard:interactive')
            ->expectsChoice('Main menu', $this->main_menu[6], $this->main_menu)
            ->assertExitCode(0)
        ;
    }

    private function createFlashcards()
    {
        Flashcard::create([
            'question' => 'How old are you?',
            'answer' => '21',
        ]);
        Flashcard::create([
            'question' => 'What is your name?',
            'answer' => 'Interactive test',
        ]);
    }
}
