<?php

namespace Plat\Interview;

use Input;
use Set;
use Cdb;
use Response;

class SurveyRepository
{
    private $deletedAnswers;

    public static function instance()
    {
        return new self();
    }

    public function getQuestions()
    {
        if (Input::get('book.setBook_id') == null) {
            $book = Set\Book::find(Input::get('book.id'));
        } else {
            $book = Set\Book::find(Input::get('book.setBook_id'));
        }

        $page = $book->questions()->where('page', Input::get('page'))->get()->load([
            'is',
            'answers.is',
            'answers.rules',
            'answers.choose',
            'parent',
            'rules'
        ])->each(function ($question) {
            if (Input::get('book.rewrite') && !Input::get('record.rewriting')) {
                $question->disabled = true;
            } elseif (Input::get('book.type') == 1 && Input::get('book.class') == 2) {
                foreach ($question->answers as $answer) {
                    if ($answer->choose != null && $answer->choose['ques_id'] ==2 && (Input::get('baby.visit') != null || Input::get('baby.now_wave') != 0)) {
                        $answer->close = true;
                    } elseif ($answer->choose != null && $answer->choose['ques_id'] ==3 && Input::get('baby.simple') != null) {
                        $answer->close = true;
                    }
                }
            }
        })->sortBy('sorter')->values();

        return ['page' => $page, 'lastPage' => $book->questions->max('page')];
    }

    public function getAnswers($user)
    {
        $this->user = $user;
        $this->install_data();

        $record = Cdb\Visit_record::find(Input::get('record.id'));

        if ($record->visit->baby_id != Input::get('record.baby_id')) {
            return Response::view('noFile', array(), 403);
        }

        $repositories = Cdb\Ques_repository::where('record_id', $record->id)->get();
        $currentPage = $repositories->isEmpty() ? 1 : $repositories->sortBy('id')->last()->question->page;

        $answers = [];
        $repositories->each(function ($repository) use (&$answers) {
            $answers[$repository->question_id] = ['id' => $repository->answer_id, 'string' => $repository->string];
        });

        return ['answers' => $answers, 'currentPage' => $currentPage];
    }

    private function install_data()
    {
        if (Input::get('book.type') == 1 && Input::get('book.class') == 1 && Input::get('book.wave.ques') == 2) {
            $install = Cdb\Baby::select('id', 'name', 'gender', 'country', 'address')->where('id', '=', Input::get('baby.id'))->first();
            Set\Book::find(Input::get('book.id'))->load('questions.answers.is.install')->questions->each(function ($ques) use (&$value, $install) {
                foreach ($ques->answers as $answer) {
                    if ($answer->improve) {
                        $column = $answer->is->install->column_name;
                        Cdb\Ques_repository::updateOrCreate(['record_id' => Input::get('record.id'), 'question_id' => $ques->id],
                                            ['answer_id' => $answer->id, 'string' => $install->$column, 'created_by' => $this->user->id, 'baby_id' => Input::get('baby.id')]);
                    }
                }
            });
        }
    }

    public function saveAnswer($user)
    {
        $this->user = $user;
        $record = Cdb\Visit_record::find(Input::get('record.id'));
        $user = Cdb\Service::where('user_id', $this->user->id)->first();
        if ($user->role != 3 && $record->visit->interviewer_id != $this->user->id) {
            return Response::view('noFile', array(), 403);
        }

        $repository = Cdb\Ques_repository::updateOrCreate([
            'record_id'   => $record->id,
            'question_id' => Input::get('question.id'),
            'baby_id'     => $record->baby_id,
        ], [
            'answer_id'  => Input::get('answer.id'),
            'string'     => Input::get('answer.string', ''),
            'created_by' => $this->user->id,
        ]);

        $this->deletedAnswers = [];
        $repository->question->answers->except(Input::get('answer_id'))->each(function ($answer) {
            $answer->childrens->each(function ($question) {
                $this->deleteAnswers($question);
            });
        });

        if ($repository->answer->rule) {
            $repository->answer->rule->skipQuestion->each(function ($question) {
                $this->deleteAnswers($question);
            });
        }

        return ['id' => $repository->answer_id, 'string' => $repository->string, 'deletedAnswers' => $this->deletedAnswers];
    }

    private function deleteAnswers($question)
    {
        Cdb\Ques_repository::where('record_id', Input::get('record.id'))->where('question_id', $question->id)->delete();
        array_push($this->deletedAnswers, $question->id);

        $question->answers->each(function ($answer) {
            $answer->childrens->each(function ($question) {
                $this->deleteAnswers($question);
            });
        });

        $question->questions->each(function ($question) {
            $this->deleteAnswers($question);
        });
    }

    public function end()
    {
        $book = Set\Book::with(['wave'])->where('wave_id', '=', Input::get('record.wave_id'))->where('quit', '=', Input::get('status'))->first();

        return ['book' => $book];
    }
}