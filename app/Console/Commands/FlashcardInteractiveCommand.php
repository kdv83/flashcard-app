<?php

namespace App\Console\Commands;

use App\Models\Flashcard;
use App\Models\FlashcardPractice;
use App\Models\Practice;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;

class FlashcardInteractiveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flashcard:interactive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Present main menu and start interactive';

    private $main_menu = [
        1 => '1. Create a flashcard',
        2 => '2. List all flashcards',
        3 => '3. Practice',
        4 => '4. Stats',
        5 => '5. Reset',
        6 => '6. Exit',
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->choice(
            'Main menu',
            $this->main_menu,
            1,
            null,
            false
        );

        switch ($name) {
            case $this->main_menu[1]:
                $this->createFlashcard();
                break;
            case $this->main_menu[2]:
                $this->getFlashcardList();
                break;
            case $this->main_menu[3]:
                $this->getPractice();
                break;
            case $this->main_menu[4]:
                $this->getStats();
                break;
            case $this->main_menu[5]:
                $this->reset();
                break;
            case $this->main_menu[6]:
                return;
        }
    }

    private function createFlashcard()
    {
        Flashcard::create([
            'question' => $this->getQuestion(),
            'answer' => $this->getAnswer(),
        ]);
        $this->info('Flashcard successfully stored!');
        $this->handle();
    }

    private function getQuestion()
    {
        $question = $this->ask('Enter a question');

        if(empty($question)){
            $this->info('Question can\'t be empty!');
            $this->handle();
        }

        return $question;
    }

    private function getAnswer()
    {
        $answer = $this->ask('Enter an answer');

        if(empty($answer)){
            $this->info('Answer can\'t be empty!');
            $this->handle();
        }

        return $answer;
    }

    private function getFlashcardList()
    {
        $this->table(['Question', 'Answer'], Flashcard::all(['question', 'answer'])->toArray());

        $this->handle();
    }

    private function getPractice()
    {
        list($practice, $flashcards) = $this->getCurrentPractice();
        $this->renderPracticeTable($practice, $flashcards);
        $this->answerTheQuestion($practice);

        $this->handle();
    }

    private function getCurrentPractice()
    {
        $flashcards = Flashcard::all();
        $practice = Practice::firstOrCreate();
        foreach ($flashcards as $flashcard) {
            if(empty($practice->practice_flashcards()->where('flashcard_id', $flashcard->id)->first())) {
                $practice->practice_flashcards()->create([
                    'flashcard_id' => $flashcard->id
                ]);
            }
        }

        return [$practice, $flashcards];
    }

    private function renderPracticeTable(Practice $practice, Collection $flashcards)
    {
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

        $table = new Table($this->output);
        $table->setHeaders([
            'Question', 'Status'
        ]);

        $separator = new TableSeparator();
        $practice_flashcards[] = $separator;
        $practice_flashcards[] = [new TableCell('% of completion ' . $percent, ['colspan' => 2])];

        $table->setRows($practice_flashcards);

        $table->render();
    }

    private function answerTheQuestion(Practice $practice)
    {
        $flashcardId = $this->ask('Choose a question');
        $flashcard = Flashcard::find($flashcardId);
        if(empty($flashcard)) $this->getPractice();
        $practice_flashcard = $practice->practice_flashcards()->where('flashcard_id', $flashcardId)->first();
        if(!empty($practice_flashcard) && $practice_flashcard->status == Flashcard::STATUSES[0]) {
            $this->info('Question correctly answered, please choose another');
            $this->handle();
        }
        $answer = $this->ask('Enter your answer');
        $currentStatus = Flashcard::STATUSES[1];
        if($answer == $flashcard->answer) {
            $currentStatus = Flashcard::STATUSES[0];
        }

        $practice_flashcard->update([
            'answer' => $answer,
            'status' => $currentStatus,
        ]);

        $this->info($currentStatus);
    }

    private function getStats()
    {
        $total = Flashcard::count();
        $practice = Practice::first();
        if(!empty($practice)) {
            $haveAnswer = $practice
                ->practice_flashcards()
                ->whereIn('status', [Flashcard::STATUSES[0], Flashcard::STATUSES[1]])->count();
            $percentHaveAnswer = round($haveAnswer / $total, 2) * 100;
            $haveCorrectAnswer = $practice
                ->practice_flashcards()
                ->where('status', Flashcard::STATUSES[0])->count();
            $percentHaveCorrectAnswer = round($haveCorrectAnswer / $total, 2) * 100;
            $this->table(['Total', 'Answered %', 'Correctly answered %'], [[$total, $percentHaveAnswer . '%', $percentHaveCorrectAnswer . '%']]);
        } else $this->error('No practice, no stats!');

        $this->handle();
    }

    private function reset()
    {
        Practice::first()->delete();

        $this->handle();
    }
}
