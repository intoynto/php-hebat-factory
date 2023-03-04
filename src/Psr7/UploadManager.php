<?php
declare (strict_types=1);

namespace Intoy\HebatFactory\Psr7;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

class UploadManager 
{
    public const ALLOWS_EXT_ALL=[
        'txt',
        'pdf',
        'xls',
        'xlsx',
        'doc',
        'docx',
        'bmp',
        'jpg',
        'jpeg',
        'png',
        'rar',
        'zip',
    ];

    public const ALLOWS_EXT_IMAGE=[
        'bmp',
        'jpg',
        'jpeg',
        'png',
        'webp', 
    ];

    public const ALLOWS_EXT_IMAGE_RESIZE=[
        'bmp',
        'jpg',
        'jpeg',
        'png',
    ];

    /**
     * @var array<UploadedFileInterface>
     */
    protected $items=[];

    /**
     * @var string
     */
    protected $prefix="";


    public function __construct(Request $request)
    {
        $this->items=$request->getUploadedFiles();        
    }

    /**
     * Set Prefix Upload
     * @param string
     * @return self
     */
    public function setPrefix($prefix)
    {
        if(is_string($prefix))
        {
            $prefix=trim((string)$prefix);
            if(strlen($prefix)>0)
            {
                $this->prefix=$prefix;
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Get Uploaded files by key_name $_FILES
     * @param string $name
     * @return null|UploadedFileInterface
     */
    public function getByName(string $name)
    {
        return isset($this->items[$name])?$this->items[$name]:null;
    }


    /**
     * Mencari informasi apakah upload item error
     * @param string $name
     * @return string|null
     */
    public function getErrorUpload($name)
    {
        $error=UPLOAD_ERR_OK;
        $f=$this->getByName($name);
        if($f instanceof UploadedFileInterface)
        {
            $error=$f->getError();
        }

        switch($error)
        {
            case UPLOAD_ERR_INI_SIZE:
                 return 'Upload file melebihi maximum upload file size';
                 break;
            case UPLOAD_ERR_FORM_SIZE:
                 return 'Upload file melebihi maximum diretive spesifik HTML form';
                 break;
            case UPLOAD_ERR_PARTIAL:
                 return 'Upload file hanya untuk partial upload';
                 break;
            case UPLOAD_ERR_NO_FILE:
                 return 'Tidak ada file upload';
                 break;
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Tidak ada temporary folder';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                return 'Gagal mencetak file ke-disk';
                break;
            case UPLOAD_ERR_EXTENSION:
                return 'File upload tidak mendukung extensi';
                break;
            default :
               return null;
               break;
        }
    }


    /**
     * Get some name is included in uploaded
     * @param string $name
     * @return bool
     */
    public function hasUpload($name)
    {
        $f=$this->getByName($name);
        if($f instanceof UploadedFileInterface)
        {
            return $f->getError()===UPLOAD_ERR_OK;
        }

        return false;
    }


    /**
     * Get some name is included in uploaded
     * @param string $name
     * @param array $extensions 
     * @return bool
     */
    public function hasAllowExtensions($name, $extensions)
    {
        $f=$this->getByName($name);
        if($f instanceof UploadedFileInterface)
        {
            $ok=$f->getError()===UPLOAD_ERR_OK;
            if(!$ok){
                return $ok;
            }

            $ext=pathinfo($f->getClientFilename(),PATHINFO_EXTENSION);
            $ext=strtolower($ext);
            return in_array($ext, $extensions);
        }

        return false;
    }


    /**
     * Move upload to directory with name
     * @param string $post_name string $_FILES key
     * @param string $directory string directory name
     * @param string $newFileName string new name of file
     * @return string
     * @throws \Exception
     */
    public function move($post_name, $directory, $newFileName="")
    {
        $f=$this->getByName($post_name);
        if(!$f instanceof UploadedFileInterface)
        {
            throw new \Exception('Invalid params. Use params instance of ServerRequestInface or UploadedFileInstarface.');
        }

        $directory=rtrim($directory,DIRECTORY_SEPARATOR);
        if(!is_dir($directory))
        {
            throw new \Exception('Directory target not exists.');
        }

        $ext=pathinfo($f->getClientFilename(),PATHINFO_EXTENSION);
        $ext=strtolower($ext);

        if(!$newFileName)
        {
            $basename = bin2hex(random_bytes(8));
            $newFileName = sprintf('%s.%0.8s', $basename, $ext);
        }
        else {
            /// test extension
            $newFileName=explode('.',$newFileName);
            if(count($newFileName)>0)
            {
                $last=strtolower(trim((string)end($newFileName)));
                $newFileName=implode('.',$newFileName);
                if($last!==$ext){
                    $newFileName.='.'.$ext;
                }
            }
            else {
                $newFileName=implode('.',$newFileName).'.'.$ext;
            }
        }

        // modify new filename
        if($this->prefix)
        {
            $newFileName=$this->prefix.'.'.$newFileName;
        }

        $f->moveTo($directory.DIRECTORY_SEPARATOR.$newFileName);
        return $newFileName;
    }


    /**
     * Handle post upload to base 64
     * @param string $post_name
     * @return string
     * @throws \Exception 
     */
    public function toBase64($post_name)
    {
        $f=$this->getByName($post_name);
        if(!$f instanceof UploadedFileInterface)
        {
            throw new \Exception('Invalid params. Use params instance of ServerRequestInface or UploadedFileInstarface.');
        }

        $extension=pathinfo($f->getClientFilename(),PATHINFO_EXTENSION);
        $extension=strtolower($extension);
        $file_type=$f->getClientMediaType();
        $stream=$f->getStream();
        $contents=$stream->getContents(); // file_get_contents($tmp);
        return 'data:'.$file_type.';base64,'.base64_encode($contents);
    }

    /**
     * @return bool
     */
    public static function removeFile($directory, $filename="")
    {
        $filename=trim((string)$filename);
        $fullfilename=$directory.$filename;
        if(strlen($filename)>0 
        && file_exists($fullfilename)
        ){
            unlink($fullfilename);
            return true;
        }
        return false;
    }
}