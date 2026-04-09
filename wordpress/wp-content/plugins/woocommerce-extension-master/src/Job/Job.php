<?php

class Job
{
	/** @var int */
	protected $actionId;

	/** @var array */
	protected $data;

	public function __construct( $postId )
	{
		$this->postId = $postId;
	}
	public function handle()
	{
		$post = get_post($this->postId );
	}
}