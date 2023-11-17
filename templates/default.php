<?php

// Default Testimionial Display Template
// $testimonial_array = (id,image,title,name,company,location,email,link,excerpt,content);

$tmCount = count($testimonial_array);

$colClass = (($tmCount %4 == 0) ? 'col-xs-12 col-sm-6 col-md-3 col-lg-3' : (($tmCount %3 == 0)  ? 'col-xs-12 col-sm-6 col-md-4 col-lg-4' : (($tmCount %2 == 0)  ? 'col-xs-12 col-sm-6 col-md-6' : 'col-xs-12 col-sm-12 col-md-12')));

$testimonialDisplay = '';
$testimonialDisplay .= '<div class="row testimonials">';

foreach ($testimonial_array as $test) {
	$testimonialDisplay .= '<div class="col '. $colClass .'">';
	if ($test['image']) {
		$testimonialDisplay .= (($test['link']) ? '<a href="'. $test['link'] .'" class="service-img-link">' : '');
		$testimonialDisplay .= (($test['image']) ? $test['image'] : '');
		$testimonialDisplay .= (($test['link']) ? '</a>' : '');
		$endLink = '';
	} 
	if ($test['link']) {
		$testimonialDisplay .= (($test['link']) ? '<a href="'. $test['link'] .'" class="testimonial-title-link">' : '');
		$endLink = '</a>'; 
	}
	$testimonialDisplay .= (($test['title']) ? '<h3 class="testimonial-name">'. $test['name'] .'</h3>' : '');
	$testimonialDisplay .= $endLink;
	$testimonialDisplay .= (($test['title']) ? '<h4 class="testimonial-title">'. $test['title'] .'</h4>' : '');
	$testimonialDisplay .= (($test['content']) ? $test['content'] : '');
	$testimonialDisplay .= '</div><!-- /col -->';
}

$testimonialDisplay .= '</div><!-- /row -->'; 	

/*
<div itemscope itemtype="http://schema.org/Product">
  <img itemprop="image" src="catcher-in-the-rye-book-cover.jpg" alt="Catcher in the Rye"/>
  <span itemprop="name">The Catcher in the Rye</span>
  <div itemprop="review" itemscope itemtype="http://schema.org/Review"> Review:
    <span itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
        <span itemprop="ratingValue">5</span> -
    </span>
    <b>"<span itemprop="name">A masterpiece of literature</span>" </b> by 
    <span itemprop="author" itemscope itemtype="http://schema.org/Person">
      <span itemprop="name">John Doe</span></span>, written on
    <meta itemprop="datePublished" content="2006-05-04">May 4, 2006
    <div itemprop="reviewBody">I really enjoyed this book. It captures the essential challenge people face as they try make sense of their lives and grow to adulthood.</div>
    <span itemprop="publisher" itemscope itemtype="http://schema.org/Organization">
        <meta itemprop="name" content="Washington Times">
    </span>
  </div>
</div>

*/			

?>