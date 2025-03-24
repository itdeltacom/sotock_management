@foreach($posts as $post)
    <div class="col-lg-4 wow fadeInUp" data-wow-delay="{{ ($loop->iteration % 3) * 0.2 + 0.1 }}s">
        <div class="blog-item">
            <div class="blog-img">
                @if($post->featured_image)
                    <img src="{{ Storage::url($post->featured_image) }}" class="img-fluid rounded-top w-100"
                        alt="{{ $post->title }}">
                @else
                    <img src="{{ asset('site/img/blog-' . rand(1, 3) . '.jpg') }}" class="img-fluid rounded-top w-100"
                        alt="{{ $post->title }}">
                @endif
            </div>
            <div class="blog-content rounded-bottom p-4">
                <div class="blog-date">{{ $post->published_at->format('d M Y') }}</div>
                <div class="blog-comment my-3">
                    <div class="small"><span class="fa fa-user text-primary"></span><span
                            class="ms-2">{{ $post->author ? $post->author->name : 'Admin' }}</span></div>
                    <div class="small"><span class="fa fa-comment-alt text-primary"></span><span
                            class="ms-2">{{ $post->comments_count ?? 0 }} Comments</span></div>
                </div>
                <a href="{{ route('blog.show', $post->slug) }}" class="h4 d-block mb-3">{{ $post->title }}</a>
                <p class="mb-3">{{ Str::limit($post->excerpt ?? strip_tags($post->content), 120) }}</p>
                <a href="{{ route('blog.show', $post->slug) }}" class="">Read More <i class="fa fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
@endforeach