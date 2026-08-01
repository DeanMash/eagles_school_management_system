<?php

namespace Tests\Feature;

use App\Helpers\Qs;
use App\Http\Requests\Student\StudentRecordCreateRequest;
use App\Http\Requests\Student\StudentRecordUpdateRequest;
use Illuminate\Foundation\Http\FormRequest;
use Tests\TestCase;

class StudentParentIdHashDecodeTest extends TestCase
{
    public function test_create_request_decodes_hashed_my_parent_id_before_validation()
    {
        $parentId = 42;
        $request = $this->makePreparedRequest(
            StudentRecordCreateRequest::class,
            ['my_parent_id' => Qs::hash($parentId)]
        );

        $this->assertSame((string) $parentId, (string) $request->input('my_parent_id'));
    }

    public function test_update_request_decodes_hashed_my_parent_id_before_validation()
    {
        $parentId = 77;
        $request = $this->makePreparedRequest(
            StudentRecordUpdateRequest::class,
            ['my_parent_id' => Qs::hash($parentId)],
            'PUT'
        );

        $this->assertSame((string) $parentId, (string) $request->input('my_parent_id'));
    }

    public function test_empty_my_parent_id_becomes_null()
    {
        $request = $this->makePreparedRequest(
            StudentRecordCreateRequest::class,
            ['my_parent_id' => '']
        );

        $this->assertNull($request->input('my_parent_id'));
    }

    public function test_create_and_update_requests_validate_decoded_parent_exists()
    {
        $create = file_get_contents(app_path('Http/Requests/Student/StudentRecordCreateRequest.php'));
        $update = file_get_contents(app_path('Http/Requests/Student/StudentRecordUpdateRequest.php'));

        $this->assertStringContainsString("'my_parent_id' => 'nullable|exists:users,id'", $create);
        $this->assertStringContainsString("'my_parent_id' => 'nullable|exists:users,id'", $update);
        $this->assertStringContainsString("Qs::decodeHash(\$input['my_parent_id'])", $create);
        $this->assertStringContainsString("Qs::decodeHash(\$input['my_parent_id'])", $update);
    }

    public function test_edit_view_selects_parent_via_my_parent_id()
    {
        $view = file_get_contents(resource_path('views/pages/support_team/students/edit.blade.php'));

        $this->assertStringContainsString('$sr->my_parent_id', $view);
        $this->assertStringNotContainsString('$sr->parent_id', $view);
    }

    private function makePreparedRequest(string $class, array $input, string $method = 'POST'): FormRequest
    {
        /** @var FormRequest $request */
        $request = $class::create('/students', $method, $input);
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make('redirect'));

        $methodRef = new \ReflectionMethod($request, 'getValidatorInstance');
        $methodRef->setAccessible(true);
        $methodRef->invoke($request);

        return $request;
    }
}
