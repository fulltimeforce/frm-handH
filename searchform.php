<form action="<?php echo home_url(); ?>" method="get" class="search-form">
    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="37" viewBox="0 0 36 37" fill="none">
        <path d="M25.1475 25.5382C19.8754 30.8103 11.3277 30.8103 6.05562 25.5382C0.783544 20.2661 0.783543 11.7184 6.05562 6.44633C11.3277 1.17425 19.8754 1.17425 25.1475 6.44633C30.4196 11.7184 30.4196 20.2661 25.1475 25.5382ZM25.1475 25.5382L34.6935 35.0842" stroke="white" stroke-width="2" />
    </svg>
    <input type="text" name="s" id="s" value="<?php the_search_query(); ?>" autocomplete="false" placeholder="Search">
    <button type="submit">
        <svg xmlns="http://www.w3.org/2000/svg" width="74" height="51" viewBox="0 0 74 51" fill="none">
            <path d="M0 25.5H72M72 25.5L54 7.5M72 25.5L54 43.5" stroke="white" stroke-width="2" />
        </svg>
    </button>
</form>