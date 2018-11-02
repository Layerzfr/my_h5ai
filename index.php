<?php
require_once 'vendor/autoload.php';

$request = $_SERVER['REQUEST_URI'];
if(isset($_POST['dir']) && !empty($_POST['dir'])){
    $dir = $_POST['dir'];
    $ajax = new Folder();
    $json = json_encode($ajax->tree(realpath("../".$dir)));
    echo $json;
    return $json;
}

class Folder
{
    private $dir;
    private $file;
    private $size;
    private $modification;
    private $folder;
    private $extension;
    private $path;
    private $name;
    private $not_found;
    private $url;
    private $previous;

    /**
     * @return mixed
     */
    public function getPrevious()
    {
        return $this->previous;
    }

    /**
     * @param mixed $previous
     * @return Folder
     */
    public function setPrevious($previous)
    {
        $this->previous = $previous;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     * @return Folder
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getNotFound()
    {
        return $this->not_found;
    }

    /**
     * @param mixed $not_found
     * @return Folder
     */
    public function setNotFound($not_found)
    {
        $this->not_found = $not_found;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Folder
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     * @return Folder
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param mixed $extension
     * @return Folder
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * @param mixed $folder
     * @return Folder
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;
        return $this;
    }


    public function setDir($dir)
    {
        $this->dir = $dir;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     * @return Folder
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param mixed $size
     * @return Folder
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getModification()
    {
        return $this->modification;
    }

    /**
     * @param mixed $modification
     * @return Folder
     */
    public function setModification($modification)
    {
        $this->modification = $modification;
        return $this;
    }

    public function fileContent($file)
    {
        $loader = new Twig_Loader_Filesystem('views');
        $twig = new Twig_Environment($loader, array(
            'cache' => 'cache',
            'debug' => true,
        ));
        if(!file_exists(realpath($file))){
            echo $twig->render('404.html.twig', array(
                'previous_url' => $this->previousUrl(),
            ));
            return;
        }
        $mime = mime_content_type($file);
        $mime_array = explode("/", $mime);
        $content = null;
        $image_path = null;

        if ($mime_array[0] === "text") {
            $content = file_get_contents(realpath($file));
        } else if ($mime_array[0] === "image") {
            $image_path = realpath($file);
            $filename = $image_path;
            $handle = fopen($filename, "rb");
            $contents = fread($handle, filesize($filename));
            fclose($handle);

            header("content-type: image/jpeg");

            echo $contents;
            return;
        }
        $pwd = realpath($file);
        $this->tree("../");
        $twig->addExtension(new Twig_Extension_Debug());
        echo $twig->render('file.html.twig', array(
            'previous_url' => $this->previousUrl(),
            'file_content' => $content,
            'image_path' => $image_path,
            'currentpwd' => $pwd,
            'tree' => $this->tree("../"),
        ));
    }


    public function getDirContents()
    {
        $filesArray = null;
        $folderArray = null;

        if (is_dir(realpath($this->dir))) {
            $dir = new DirectoryIterator(realpath($this->dir));
        } else {
            $this->fileContent($this->dir);
            return;
        }
        $pwd = $dir->getPath();
        foreach ($dir as $key => $value) {
            $this->file = $value;
            if (($this->file->getFileName() === ".." && $key === 1) || ($this->file->getFilename() === "." && $key === 0)) {
                continue;
            }
            if ($value->isFile()) {
                $this->setSize($this->file->getSize());
                $this->setModification(date("F d Y H:i:s.", $this->file->getMTime()));
                $this->setExtension($this->file->getExtension());
                $this->setPath($this->file->getPath());
                $this->setName($this->file->getFilename());
                if (!file_exists("assets/icon/" . $this->getExtension() . ".png")) {
                    $this->setNotFound("assets/icon/file.png");
                } else {
                    $this->setNotFound(NULL);
                }
                $filesArray[] = array(
                    'filename' => $this->name,
                    'size' => $this->size,
                    'modification' => $this->modification,
                    'extension' => $this->extension,
                    'path' => $this->path,
                    'file_not_found' => $this->not_found,
                );
            } else {
                $this->setFolder($this->file->getFilename());
                $folderArray[] = array(
                    'foldername' => $this->folder,
                );
            }
        }
        $request = $_SERVER['REQUEST_URI'];
        if ($request === "/") {
            $this->setUrl(null);
            $this->setPrevious(null);
        } else {
            $this->setUrl(substr($_SERVER['REQUEST_URI'], 1));

            $this->setPrevious('');
        }
        $loader = new Twig_Loader_Filesystem('views');
        $twig = new Twig_Environment($loader, array(
            'cache' => 'cache',
            'debug' => true,
        ));
        $twig->addExtension(new Twig_Extension_Debug());
        $this->tree("../");
        echo $twig->render('folder.html.twig', array(
            'folders' => $folderArray,
            'files' => $filesArray,
            'current_url' => $this->url,
            'currentpwd' => $pwd,
            'previous_url' => $this->previousUrl(),
            'tree' => $this->tree("../"),
            'slash' => $this->slash(),
        ));
    }

    public function previousUrl()
    {
        $url = substr($_SERVER['REQUEST_URI'], 1);
        $url_array = explode("/", $url);
        $url_count = count($url_array) - 1;
        if ($url_array[$url_count] === "") {
            unset($url_array[$url_count]);
        }
        unset($url_array[count($url_array) - 1]);
        $previous_url = "/" . implode("/", $url_array);
        return $previous_url;
    }

    public function slash(){
        if(substr($_SERVER['REQUEST_URI'], -1) !== "/"){
            $slash = "/";
        }else{
            $slash = null;
        }
        return $slash;
    }

    public function tree($directory = "../"){
        if(is_dir($directory)) {
            $scan = scandir($directory);
            $folders = array();
            $files = array();
            unset($scan[array_search('.', $scan, true)]);
            unset($scan[array_search('..', $scan, true)]);
            foreach($scan as $file){
                if(is_dir($directory."/".$file)){
                    array_push($folders, $file);
                }else{
                    array_push($files, $file);
                }
            }
            $arrayTree = array('folder' => $folders, "file" => $files);
            return $arrayTree;
        }else{
            return false;
        }
    }

}

$dir = new Folder();
$dir->setDir('..' . $request);
$dir->getDirContents();
