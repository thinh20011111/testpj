<?php

namespace App\Repositories\Product;

use App\Repositories\BaseRepository;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request as HttpRequest;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{

  public function getModel()
  {
    return Product::class;
  }

  public function getRelatedProducts($product, $limit = 4)
  {
    return $this->model->where('product_category_id', $product->product_category_id)
      ->where('tag', $product->tag)
      ->limit($limit)
      ->get();
  }

  public function getFeatureProductByCategory(int $categoryId)
  {
    return $this->model->where('featured', true)
      ->where('product_category_id', $categoryId)
      ->get();
  }

  public function getProductOnIndex($request)
  {
    $search = $request->search ?? '';
    $products = $this->model->where('name', 'like', '%' . $search . '%');
    $products = $this->filter($products, $request);
    $products = $this->sortAndPagination($products, $request);
    
    return $products;
  }

  public function getProductByCategory($categoryName, $request)
  {
    $products = ProductCategory::where('name', $categoryName)->first()->products->toQuery();
    $products = $this->filter($products, $request);
    $products = $this->sortAndPagination($products, $request);

    return $products;
  }

  private function sortAndPagination($products, HttpRequest $request)
  {
    $perPage = $request->show ?? 15;
    $sortBy = $request->sort_by ?? 'latest';
    
    switch ($sortBy) {
      case 'latest':
        $products = $products->orderBy('id');
        break;
      case 'oldest':
        $products = $products->orderByDesc('id');
        break;
      case 'name-ascending':
        $products = $products->orderBy('name');
        break;
      case 'name-descending':
        $products = $products->orderByDesc('name');
        break;
      case 'price-ascending':
        $products = $products->orderBy('price');
        break;
      case 'price-descending':
        $products = $products->orderByDesc('price');
        break;
      default:
        $products = $products->orderBy('id');
        break;
    }

    $products = $products->paginate($perPage);
    $products->appends(['sort_by' => $sortBy, 'show => $perPage']);

    return $products;
  }

  private function filter($products, HttpRequest $request)
  {
    //Trademark:
    $trademarks = $request->trademark ?? [];
    $trademark_ids = array_keys($trademarks);
    $products  = $trademark_ids != null ? $products->whereIn('trademark_id', $trademark_ids) : $products;

    //Price:
    $priceMin = $request->price_min;
    $priceMax = $request->price_max;
    $priceMin = str_replace('VNĐ', '', $priceMin);
    $priceMax = str_replace('VNĐ', '', $priceMax);
    $products = ($priceMin != null && $priceMax != null) 
        ? $products->whereBetween('price', [$priceMin, $priceMax])
        : $products;

    //Color:
    $color = $request->color;
    $products = $color != null
      ? $products->whereHas('productDetails', function ($query) use ($color) {
        return $query->where('color', $color)->where('qty', '>', 0);
      })
      : $products;

    return $products;
  }
}
