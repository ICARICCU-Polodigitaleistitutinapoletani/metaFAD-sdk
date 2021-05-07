<?php
interface metafad_dam_services_ImportMediaInterface
{
    public function mediaExists($filePath);

    public function insertMedia($mediaData,$type = 'media');

    public function streamUrl($id, $stream);

    public function mediaUrl($id);

    public function getJSON($id, $title);

    public function addMediaToContainer($magName, $medias, $cover, $tag = null);

    public function insertBytestream($data,$id);

	public function resizeStreamUrl($id, $stream, $w);

    public function resizeStreamUrlLocal($id, $stream, $w);

	public function resizeInfoLocal($id, $stream, $w);

    public function streamUrlLocal($id, $stream);

	public function deleteContainer($id, $removeContainedMedia=false);

	public function search($title);

	public function getInstance();

	public function correctInstance($id);

	public function saveMediaDatastream($mediaId, $mediaData,$datastreamName,$datastreamId = null, $method = null);
}
