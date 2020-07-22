<?php

namespace App\Http\Traits;

/**
* Trait destinado para manejo de archivos en todo el sistema
*/
trait Fileable {

    /**
     * Slugifies Strings - Used for storing file's names
     * @param String $text
     * @return String $slugified_text
     */
    public static function slugify($text) : string
    {
      // replace non letter or digits by -
      $text = preg_replace('~[^\pL\d]+~u', '-', $text);

      // transliterate
      $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

      // remove unwanted characters
      $text = preg_replace('~[^-\w]+~', '', $text);

      // trim
      $text = trim($text, '-');

      // remove duplicate -
      $text = preg_replace('~-+~', '-', $text);

      // lowercase
      $text = strtolower($text);

      if (empty($text)) {
        return 'n-a';
      }

      return $text;
    }

    /**
     * Verifies if folder exists
     * If it exists, it cleans the folder
     * Otherwise it creates the specified folder
     */
    public function checkDestinationPath ( $destinationPath )
    {
        ( \File::exists( $destinationPath ) ) ? \File::cleanDirectory( $destinationPath ) : \File::makeDirectory( $destinationPath, 0777, true );

        return true;
    }

    /**
     * Creates the specified folder,
     * If it already exists it leaves it the same way
     */
    public function createDestinationPath ( $destinationPath ) : void
    {
        ( !\File::exists( storage_path('/app/public/' . $destinationPath) ) ) ?  \File::makeDirectory( storage_path('/app/public/' . $destinationPath), 0777, true ) : true;
    }

    /**
     * Transforms file name to a slug
     * @param $file_name File Name, $extension File extension
     * @return String $file_name
     */
    public function slugFileName($file_name, $extension) : string
    {
        return \Str::slug($file_name) . '.' . $extension;
        // return $this->slugify($file_name) . '.' . $extension;
    }

    /**
     * Crea directorio y almacena el archivo
     * @param $destinationPath es la ruta donde se almacenará, $file es el archivo como se recibe en el request
     * @return true
     */
    public function moveFile($destinationPath, $file)
    {
        // 1) Genera el nombre del archivo
        $file_name = $this->slugFileName($file->getClientOriginalName(), $file->getClientOriginalExtension());

        // 2) Crea el directorio
        ( !\File::exists( $destinationPath ) ) ? \File::makeDirectory( $destinationPath, 0777, true ) : true;

         // 3) Almacena archivo
        $file->move($destinationPath, $file_name);

        return true;
    }

    /**
     * Returns file name
     * @param File $file 
     * @param String $file_name : optional
     * @return String $file_name slugified file name
     */
    public function setFileName($file, $file_name, $should_slug) : string
    {
        // If file name it's not defined
        if ( $file_name == null ) { // Takes out the extension and just gets the file name
            $file_name = basename($file->getClientOriginalName(), '.'.$file->getClientOriginalExtension());
        }
        
        // Returns file name as a slug
        if ($should_slug) {
            return $this->slugFileName($file_name, $file->getClientOriginalExtension());
        }

        return $file_name . '.' . $file->getClientOriginalExtension();
    }
    
    /**
     * Stores a new file with a new name or mantaining the original
     * @param String $route route where file shall be stored, 
     * @param File $file The file that will be stored, 
     * @param String $file_name The name that should be assigned to the file
     *                              Optional
     *                              Default: null
     * @param Boolean $should_slug Determines whether the file name should be slugged or not
     *                             Optional
     *                             Default: true, 
     * @return String $file_name
     */
    public function newFile($route, $file, $file_name = null, $should_slug = true) : string
    {
        // Genera el nombre del archivo como un slug
        $file_name = $this->setFileName($file, $file_name, $should_slug);

        // Genera ruta del archivo para almacenar el archivo
        $destinationPath = storage_path( '/app/public/' . strtolower($route) );

        // Verifica que la carpeta exista, de lo contrario la crea
        ( !\File::exists( $destinationPath ) ) ? \File::makeDirectory( $destinationPath, 0777, true ) : true;

         // Almacena archivo
        $file->move($destinationPath, $file_name);

        return $file_name;
    }

    /**
     * Deletes a specified File
     * @param String $file_route string including the route and file's name 
     * @return Boolean true
     */
    public function deleteFile($file_route) : bool
    {
        // Define la ruta del archivo que se eliminará
        $deleting_file = storage_path( '/app/public/' . strtolower($file_route) );

        //Eliminar archivo correspondiente al registro
        if ( \File::exists($deleting_file) ) {  
            \File::delete($deleting_file);
        }
        
        return true;
    }

    /**
     * Delete Recursively a Directory
     * @param string $directory 
     */
    public function deleteDirectory(string $directory) {
        $base_path = storage_path('/app/public/');

        return ( \File::exists( $base_path . $directory) ) ?  \File::deleteDirectory( $base_path . $directory ) : true;
    }

    /**
     * Deletes the file content, but keeps the file available
     */
    public function clenDirectory(string $directory) {
        $base_path = storage_path('/app/public/');

        return (\File::exists( $base_path . $directory)) ? \File::cleanDirectory( $base_path . $directory ) : true;
    }
}
