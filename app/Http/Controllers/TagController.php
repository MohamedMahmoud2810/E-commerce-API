<?php

namespace App\Http\Controllers;

use App\DTOs\GetTagDTO;
use App\DTOs\StoreTagDTO;
use App\Http\Requests\GetTagRequest;
use App\Http\Requests\StoreTagRequest;
use App\Http\Resources\TagCollection;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Services\TagService;
use App\Services\TagServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @OA\Tag(
 *     name="Tag Management",
 *     description="Endpoints for managing tags"
 * )
 */

 /**
 * @OA\Schema(
 *     schema="Tag",
 *     type="object",
 *     title="Tag",
 *     description="Tag model",
 *     properties={
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Sample Tag"),
 *         @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-01T12:00:00Z"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-01T12:00:00Z")
 *     }
 * )
 */
class TagController extends Controller
{
    

    public function __construct(protected TagService $tagService)
    {
        
    }
    /**
     * @OA\Get(
     *     path="/api/tags",
     *     operationId="indexTags",
     *     tags={"Tag Management"},
     *     summary="List all tags",
     *     description="Retrieves a list of all tags.",
     *     @OA\Response(
     *         response=200,
     *         description="List of tags",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Tag")
     *         )
     *     )
     * )
     */
    public function index(GetTagRequest $request)
    {
        $dto = new GetTagDTO($request->all());
        $tags = $this->tagService->getAllTags($dto);
        return new TagCollection($tags);
    }
    /**
     * @OA\Post(
     *     path="/api/tags",
     *     operationId="storeTag",
     *     tags={"Tag Management"},
     *     summary="Create a new tag",
     *     description="Creates a new tag with the provided data.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="New Tag", description="The name of the tag")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tag created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Tag")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */
    public function store(StoreTagRequest $request)
    {
        $dto = new StoreTagDTO($request->all());
        $tag = $this->tagService->createTag($dto);
        return new TagResource($tag);
    }
 /**
     * @OA\Get(
     *     path="/api/tags/{id}",
     *     operationId="showTag",
     *     tags={"Tag Management"},
     *     summary="Get a specific tag",
     *     description="Retrieves the tag with the specified ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the tag to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag details",
     *         @OA\JsonContent(ref="#/components/schemas/Tag")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found"
     *     )
     * )
     */
    public function show($id)
    {
        $tag = $this->tagService->getTagById($id);
        return response()->json($tag, Response::HTTP_OK);
    }
/**
     * @OA\Put(
     *     path="/api/tags/{id}",
     *     operationId="updateTag",
     *     tags={"Tag Management"},
     *     summary="Update a tag",
     *     description="Updates the tag with the specified ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the tag to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Tag", description="The updated name of the tag")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Tag")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found"
     *     )
     * )
     */

        public function update(StoreTagRequest $request, $id)
        {
            $dto = new StoreTagDTO($request->all());
            $tag = $this->tagService->updateTag($id, $dto);
            return response()->json($tag, Response::HTTP_OK);
        }
 /**
     * @OA\Delete(
     *     path="/api/tags/{id}",
     *     operationId="destroyTag",
     *     tags={"Tag Management"},
     *     summary="Delete a tag",
     *     description="Deletes the tag with the specified ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the tag to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tag deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $deleted = $this->tagService->deleteTag($id);

        if (!$deleted) {
            return response()->json(['message' => 'Tag not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['message' => 'Tag deleted'], Response::HTTP_OK);
    }

}
