<?php

// return FieldsetFactory::withFields([
//     ['handle' => 'test', 'field' => ['type' => 'baz']],
// ])->create();

// test('defines statamic modifiers', function () {
//     $this->latte('I like {$things|sentence_list}', ['things' => ['a', 'b', 'c']])
//         ->assertSee('I like a, b, and c');

//     $this->latte('{if ("ABC"|is_uppercase)} YES {else} NO {/if}')
//         ->assertSee('YES');

//     $this->latte('{("Just Because I Can"|dashify)}')
//         ->assertSee('just-because-i-can');
// });

// test('does not overwrite existing filters', function () {
//     Latte::addFilter('dashify', fn ($str) => $str);

//     $this->latte('{("Just Because I Can"|dashify)}')
//         ->assertSee('Just Because I Can')
//         ->assertDontSee('just-because-i-can');
// });
