
class MyUploadAdapter {
	constructor( loader ) {
		// The file loader instance to use during the upload.
		this.loader = loader;
	}

	// Starts the upload process.
	upload() {
		// return this.loader.file
		// 	.then( file => new Promise( ( resolve, reject ) => {
		// 		const reader = new FileReader();
		// 	    reader.readAsDataURL(file);
		// 	    reader.onload = () => resolve([reader.result]);
		// 	    reader.onerror = error => reject(error);
		// 	} ) );
		return new Promise( ( resolve, reject ) => {
				const reader = this.reader = new window.FileReader();

				reader.addEventListener( 'load', () => {
					resolve( { default: reader.result } );
				} );

				reader.addEventListener( 'error', err => {
					reject( err );
				} );

				reader.addEventListener( 'abort', () => {
					reject(
		'alignment', '|',);
				} );

				this.loader.file.then( file => {
					reader.readAsDataURL( file );
				} );
	})}

	/**
		 * Aborts the upload process.
		 *
		 * @see module:upload/filerepository~UploadAdapter#abort
		 * @returns {Promise}
		 */
		abort() {
			this.reader.abort();
		}
}

// ...

function MyCustomUploadAdapterPlugin( editor ) {
	editor.plugins.get( 'FileRepository' ).createUploadAdapter = ( loader ) => {
		// Configure the URL to the upload script in your back-end here!
		return new MyUploadAdapter( loader );
	};
}

//         defPlugins = [
//     "selectAll",
//     "undo",
//     "redo",
//     "bold",
//     "italic",
//     "blockQuote",
//     "imageTextAlternative",
//     "link",
//     "ckfinder",
//     "uploadImage",
//     "imageUpload",
//     "heading",
//     "imageStyle:full",
//     "imageStyle:alignLeft",
//     "imageStyle:alignRight",
//     "indent",
//     "outdent",
//     "numberedList",
//     "bulletedList",
//     "mediaEmbed",
//     "insertTable",
//     "tableColumn",
//     "tableRow",
//     "mergeTableCells",
// 	"alignLeft","alignRight","alignCenter"
// ];

const addEditPart = (window.addEditPart = function addEditPart(to,defPlugins,placeholder="",finally_fn=-1){
	finally_fn === -1 && (finally_fn=function(){});

	InlineEditor
		.create( document.querySelector( to ),
			 {
			  extraPlugins: [ MyCustomUploadAdapterPlugin ],
	toolbar: defPlugins,
	language: "pt-br",
	heading: {
		options: [
			{ model: 'heading1', view: 'h1', title: 'Titulo', class: 'ck-heading_heading1' },
			{ model: 'heading4', view: 'h4', title: 'Sub-titulo', class: 'ck-heading_heading4' }
		]
	},
	alignment: {
	   options: [ 'left', 'right', 'center' ]
   },
	placeholder: placeholder,
	image: {
		// You need to configure the image toolbar, too, so it uses the new style buttons.
		toolbar: [ 'imageTextAlternative', '|', 'imageStyle:alignLeft', 'imageStyle:full', 'imageStyle:alignRight' ],

		styles: [
			// This option is equal to a situation where no style is applied.
			'full',

			// This represents an image aligned to the left.
			'alignLeft',

			// This represents an image aligned to the right.
			'alignRight'
		]
	}

}).then((newEditor)=>{ window[to.split("=")[1].split("]")[0]] = newEditor; })
	.catch( error => {
		console.error( error );
	} ).finally(()=>finally_fn(window[to.split("=")[1].split("]")[0]]));
});
