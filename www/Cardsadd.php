<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start(); // Turn on output buffering
?>
<?php include_once "ewcfg10.php" ?>
<?php include_once "ewmysql10.php" ?>
<?php include_once "phpfn10.php" ?>
<?php include_once "Cardsinfo.php" ?>
<?php include_once "userfn10.php" ?>
<?php

//
// Page class
//

$Cards_add = NULL; // Initialize page object first

class cCards_add extends cCards {

	// Page ID
	var $PageID = 'add';

	// Project ID
	var $ProjectID = "{A465A203-2D9F-40EC-9F78-053F2F1FA743}";

	// Table name
	var $TableName = 'Cards';

	// Page object name
	var $PageObjName = 'Cards_add';

	// Page name
	function PageName() {
		return ew_CurrentPage();
	}

	// Page URL
	function PageUrl() {
		$PageUrl = ew_CurrentPage() . "?";
		if ($this->UseTokenInUrl) $PageUrl .= "t=" . $this->TableVar . "&"; // Add page token
		return $PageUrl;
	}

	// Message
	function getMessage() {
		return @$_SESSION[EW_SESSION_MESSAGE];
	}

	function setMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_MESSAGE], $v);
	}

	function getFailureMessage() {
		return @$_SESSION[EW_SESSION_FAILURE_MESSAGE];
	}

	function setFailureMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_FAILURE_MESSAGE], $v);
	}

	function getSuccessMessage() {
		return @$_SESSION[EW_SESSION_SUCCESS_MESSAGE];
	}

	function setSuccessMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_SUCCESS_MESSAGE], $v);
	}

	function getWarningMessage() {
		return @$_SESSION[EW_SESSION_WARNING_MESSAGE];
	}

	function setWarningMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_WARNING_MESSAGE], $v);
	}

	// Show message
	function ShowMessage() {
		$hidden = FALSE;
		$html = "";

		// Message
		$sMessage = $this->getMessage();
		$this->Message_Showing($sMessage, "");
		if ($sMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sMessage;
			$html .= "<div class=\"alert alert-success ewSuccess\">" . $sMessage . "</div>";
			$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message in Session
		}

		// Warning message
		$sWarningMessage = $this->getWarningMessage();
		$this->Message_Showing($sWarningMessage, "warning");
		if ($sWarningMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sWarningMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sWarningMessage;
			$html .= "<div class=\"alert alert-warning ewWarning\">" . $sWarningMessage . "</div>";
			$_SESSION[EW_SESSION_WARNING_MESSAGE] = ""; // Clear message in Session
		}

		// Success message
		$sSuccessMessage = $this->getSuccessMessage();
		$this->Message_Showing($sSuccessMessage, "success");
		if ($sSuccessMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sSuccessMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sSuccessMessage;
			$html .= "<div class=\"alert alert-success ewSuccess\">" . $sSuccessMessage . "</div>";
			$_SESSION[EW_SESSION_SUCCESS_MESSAGE] = ""; // Clear message in Session
		}

		// Failure message
		$sErrorMessage = $this->getFailureMessage();
		$this->Message_Showing($sErrorMessage, "failure");
		if ($sErrorMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sErrorMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sErrorMessage;
			$html .= "<div class=\"alert alert-error ewError\">" . $sErrorMessage . "</div>";
			$_SESSION[EW_SESSION_FAILURE_MESSAGE] = ""; // Clear message in Session
		}
		echo "<table class=\"ewStdTable\"><tr><td><div class=\"ewMessageDialog\"" . (($hidden) ? " style=\"display: none;\"" : "") . ">" . $html . "</div></td></tr></table>";
	}
	var $PageHeader;
	var $PageFooter;

	// Show Page Header
	function ShowPageHeader() {
		$sHeader = $this->PageHeader;
		$this->Page_DataRendering($sHeader);
		if ($sHeader <> "") { // Header exists, display
			echo "<p>" . $sHeader . "</p>";
		}
	}

	// Show Page Footer
	function ShowPageFooter() {
		$sFooter = $this->PageFooter;
		$this->Page_DataRendered($sFooter);
		if ($sFooter <> "") { // Footer exists, display
			echo "<p>" . $sFooter . "</p>";
		}
	}

	// Validate page request
	function IsPageRequest() {
		global $objForm;
		if ($this->UseTokenInUrl) {
			if ($objForm)
				return ($this->TableVar == $objForm->GetValue("t"));
			if (@$_GET["t"] <> "")
				return ($this->TableVar == $_GET["t"]);
		} else {
			return TRUE;
		}
	}

	//
	// Page class constructor
	//
	function __construct() {
		global $conn, $Language;
		$GLOBALS["Page"] = &$this;

		// Language object
		if (!isset($Language)) $Language = new cLanguage();

		// Parent constuctor
		parent::__construct();

		// Table object (Cards)
		if (!isset($GLOBALS["Cards"]) || get_class($GLOBALS["Cards"]) == "cCards") {
			$GLOBALS["Cards"] = &$this;
			$GLOBALS["Table"] = &$GLOBALS["Cards"];
		}

		// Page ID
		if (!defined("EW_PAGE_ID"))
			define("EW_PAGE_ID", 'add', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EW_TABLE_NAME"))
			define("EW_TABLE_NAME", 'Cards', TRUE);

		// Start timer
		if (!isset($GLOBALS["gTimer"])) $GLOBALS["gTimer"] = new cTimer();

		// Open connection
		if (!isset($conn)) $conn = ew_Connect();
	}

	// 
	//  Page_Init
	//
	function Page_Init() {
		global $gsExport, $gsExportFile, $UserProfile, $Language, $Security, $objForm;

		// Security
		$Security = new cAdvancedSecurity();
		if (!$Security->IsLoggedIn()) $Security->AutoLogin();
		if (!$Security->IsLoggedIn()) {
			$Security->SaveLastUrl();
			$this->Page_Terminate("login.php");
		}

		// Create form object
		$objForm = new cFormObj();
		$this->CurrentAction = (@$_GET["a"] <> "") ? $_GET["a"] : @$_POST["a_list"]; // Set up curent action

		// Global Page Loading event (in userfn*.php)
		Page_Loading();

		// Page Load event
		$this->Page_Load();
	}

	//
	// Page_Terminate
	//
	function Page_Terminate($url = "") {
		global $conn;

		// Page Unload event
		$this->Page_Unload();

		// Global Page Unloaded event (in userfn*.php)
		Page_Unloaded();
		$this->Page_Redirecting($url);

		 // Close connection
		$conn->Close();

		// Go to URL if specified
		if ($url <> "") {
			if (!EW_DEBUG_ENABLED && ob_get_length())
				ob_end_clean();
			header("Location: " . $url);
		}
		exit();
	}
	var $DbMasterFilter = "";
	var $DbDetailFilter = "";
	var $Priv = 0;
	var $OldRecordset;
	var $CopyRecord;

	// 
	// Page main
	//
	function Page_Main() {
		global $objForm, $Language, $gsFormError;

		// Process form if post back
		if (@$_POST["a_add"] <> "") {
			$this->CurrentAction = $_POST["a_add"]; // Get form action
			$this->CopyRecord = $this->LoadOldRecord(); // Load old recordset
			$this->LoadFormValues(); // Load form values
		} else { // Not post back

			// Load key values from QueryString
			$this->CopyRecord = TRUE;
			if (@$_GET["id"] != "") {
				$this->id->setQueryStringValue($_GET["id"]);
				$this->setKey("id", $this->id->CurrentValue); // Set up key
			} else {
				$this->setKey("id", ""); // Clear key
				$this->CopyRecord = FALSE;
			}
			if ($this->CopyRecord) {
				$this->CurrentAction = "C"; // Copy record
			} else {
				$this->CurrentAction = "I"; // Display blank record
				$this->LoadDefaultValues(); // Load default values
			}
		}

		// Set up Breadcrumb
		$this->SetupBreadcrumb();

		// Validate form if post back
		if (@$_POST["a_add"] <> "") {
			if (!$this->ValidateForm()) {
				$this->CurrentAction = "I"; // Form error, reset action
				$this->EventCancelled = TRUE; // Event cancelled
				$this->RestoreFormValues(); // Restore form values
				$this->setFailureMessage($gsFormError);
			}
		}

		// Perform action based on action code
		switch ($this->CurrentAction) {
			case "I": // Blank record, no action required
				break;
			case "C": // Copy an existing record
				if (!$this->LoadRow()) { // Load record based on key
					if ($this->getFailureMessage() == "") $this->setFailureMessage($Language->Phrase("NoRecord")); // No record found
					$this->Page_Terminate("Cardslist.php"); // No matching record, return to list
				}
				break;
			case "A": // Add new record
				$this->SendEmail = TRUE; // Send email on add success
				if ($this->AddRow($this->OldRecordset)) { // Add successful
					if ($this->getSuccessMessage() == "")
						$this->setSuccessMessage($Language->Phrase("AddSuccess")); // Set up success message
					$sReturnUrl = $this->getReturnUrl();
					if (ew_GetPageName($sReturnUrl) == "Cardsview.php")
						$sReturnUrl = $this->GetViewUrl(); // View paging, return to view page with keyurl directly
					$this->Page_Terminate($sReturnUrl); // Clean up and return
				} else {
					$this->EventCancelled = TRUE; // Event cancelled
					$this->RestoreFormValues(); // Add failed, restore form values
				}
		}

		// Render row based on row type
		$this->RowType = EW_ROWTYPE_ADD;  // Render add type

		// Render row
		$this->ResetAttrs();
		$this->RenderRow();
	}

	// Get upload files
	function GetUploadFiles() {
		global $objForm;

		// Get upload data
	}

	// Load default values
	function LoadDefaultValues() {
		$this->CardId->CurrentValue = NULL;
		$this->CardId->OldValue = $this->CardId->CurrentValue;
		$this->Name->CurrentValue = NULL;
		$this->Name->OldValue = $this->Name->CurrentValue;
		$this->Surname->CurrentValue = NULL;
		$this->Surname->OldValue = $this->Surname->CurrentValue;
		$this->Phone->CurrentValue = NULL;
		$this->Phone->OldValue = $this->Phone->CurrentValue;
		$this->Active->CurrentValue = 1;
	}

	// Load form values
	function LoadFormValues() {

		// Load from form
		global $objForm;
		if (!$this->CardId->FldIsDetailKey) {
			$this->CardId->setFormValue($objForm->GetValue("x_CardId"));
		}
		if (!$this->Name->FldIsDetailKey) {
			$this->Name->setFormValue($objForm->GetValue("x_Name"));
		}
		if (!$this->Surname->FldIsDetailKey) {
			$this->Surname->setFormValue($objForm->GetValue("x_Surname"));
		}
		if (!$this->Phone->FldIsDetailKey) {
			$this->Phone->setFormValue($objForm->GetValue("x_Phone"));
		}
		if (!$this->Active->FldIsDetailKey) {
			$this->Active->setFormValue($objForm->GetValue("x_Active"));
		}
	}

	// Restore form values
	function RestoreFormValues() {
		global $objForm;
		$this->LoadOldRecord();
		$this->CardId->CurrentValue = $this->CardId->FormValue;
		$this->Name->CurrentValue = $this->Name->FormValue;
		$this->Surname->CurrentValue = $this->Surname->FormValue;
		$this->Phone->CurrentValue = $this->Phone->FormValue;
		$this->Active->CurrentValue = $this->Active->FormValue;
	}

	// Load row based on key values
	function LoadRow() {
		global $conn, $Security, $Language;
		$sFilter = $this->KeyFilter();

		// Call Row Selecting event
		$this->Row_Selecting($sFilter);

		// Load SQL based on filter
		$this->CurrentFilter = $sFilter;
		$sSql = $this->SQL();
		$res = FALSE;
		$rs = ew_LoadRecordset($sSql);
		if ($rs && !$rs->EOF) {
			$res = TRUE;
			$this->LoadRowValues($rs); // Load row values
			$rs->Close();
		}
		return $res;
	}

	// Load row values from recordset
	function LoadRowValues(&$rs) {
		global $conn;
		if (!$rs || $rs->EOF) return;

		// Call Row Selected event
		$row = &$rs->fields;
		$this->Row_Selected($row);
		$this->id->setDbValue($rs->fields('id'));
		$this->CardId->setDbValue($rs->fields('CardId'));
		$this->Name->setDbValue($rs->fields('Name'));
		$this->Surname->setDbValue($rs->fields('Surname'));
		$this->Phone->setDbValue($rs->fields('Phone'));
		$this->Active->setDbValue($rs->fields('Active'));
	}

	// Load DbValue from recordset
	function LoadDbValues(&$rs) {
		if (!$rs || !is_array($rs) && $rs->EOF) return;
		$row = is_array($rs) ? $rs : $rs->fields;
		$this->id->DbValue = $row['id'];
		$this->CardId->DbValue = $row['CardId'];
		$this->Name->DbValue = $row['Name'];
		$this->Surname->DbValue = $row['Surname'];
		$this->Phone->DbValue = $row['Phone'];
		$this->Active->DbValue = $row['Active'];
	}

	// Load old record
	function LoadOldRecord() {

		// Load key values from Session
		$bValidKey = TRUE;
		if (strval($this->getKey("id")) <> "")
			$this->id->CurrentValue = $this->getKey("id"); // id
		else
			$bValidKey = FALSE;

		// Load old recordset
		if ($bValidKey) {
			$this->CurrentFilter = $this->KeyFilter();
			$sSql = $this->SQL();
			$this->OldRecordset = ew_LoadRecordset($sSql);
			$this->LoadRowValues($this->OldRecordset); // Load row values
		} else {
			$this->OldRecordset = NULL;
		}
		return $bValidKey;
	}

	// Render row values based on field settings
	function RenderRow() {
		global $conn, $Security, $Language;
		global $gsLanguage;

		// Initialize URLs
		// Call Row_Rendering event

		$this->Row_Rendering();

		// Common render codes for all row types
		// id
		// CardId
		// Name
		// Surname
		// Phone
		// Active

		if ($this->RowType == EW_ROWTYPE_VIEW) { // View row

			// id
			$this->id->ViewValue = $this->id->CurrentValue;
			$this->id->ViewCustomAttributes = "";

			// CardId
			$this->CardId->ViewValue = $this->CardId->CurrentValue;
			$this->CardId->ViewCustomAttributes = "";

			// Name
			$this->Name->ViewValue = $this->Name->CurrentValue;
			$this->Name->ViewCustomAttributes = "";

			// Surname
			$this->Surname->ViewValue = $this->Surname->CurrentValue;
			$this->Surname->ViewCustomAttributes = "";

			// Phone
			$this->Phone->ViewValue = $this->Phone->CurrentValue;
			$this->Phone->ViewCustomAttributes = "";

			// Active
			$this->Active->ViewValue = $this->Active->CurrentValue;
			$this->Active->ViewCustomAttributes = "";

			// CardId
			$this->CardId->LinkCustomAttributes = "";
			$this->CardId->HrefValue = "";
			$this->CardId->TooltipValue = "";

			// Name
			$this->Name->LinkCustomAttributes = "";
			$this->Name->HrefValue = "";
			$this->Name->TooltipValue = "";

			// Surname
			$this->Surname->LinkCustomAttributes = "";
			$this->Surname->HrefValue = "";
			$this->Surname->TooltipValue = "";

			// Phone
			$this->Phone->LinkCustomAttributes = "";
			$this->Phone->HrefValue = "";
			$this->Phone->TooltipValue = "";

			// Active
			$this->Active->LinkCustomAttributes = "";
			$this->Active->HrefValue = "";
			$this->Active->TooltipValue = "";
		} elseif ($this->RowType == EW_ROWTYPE_ADD) { // Add row

			// CardId
			$this->CardId->EditCustomAttributes = "";
			$this->CardId->EditValue = ew_HtmlEncode($this->CardId->CurrentValue);
			$this->CardId->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->CardId->FldCaption()));

			// Name
			$this->Name->EditCustomAttributes = "";
			$this->Name->EditValue = ew_HtmlEncode($this->Name->CurrentValue);
			$this->Name->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->Name->FldCaption()));

			// Surname
			$this->Surname->EditCustomAttributes = "";
			$this->Surname->EditValue = ew_HtmlEncode($this->Surname->CurrentValue);
			$this->Surname->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->Surname->FldCaption()));

			// Phone
			$this->Phone->EditCustomAttributes = "";
			$this->Phone->EditValue = ew_HtmlEncode($this->Phone->CurrentValue);
			$this->Phone->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->Phone->FldCaption()));

			// Active
			$this->Active->EditCustomAttributes = "";
			$this->Active->EditValue = ew_HtmlEncode($this->Active->CurrentValue);
			$this->Active->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->Active->FldCaption()));

			// Edit refer script
			// CardId

			$this->CardId->HrefValue = "";

			// Name
			$this->Name->HrefValue = "";

			// Surname
			$this->Surname->HrefValue = "";

			// Phone
			$this->Phone->HrefValue = "";

			// Active
			$this->Active->HrefValue = "";
		}
		if ($this->RowType == EW_ROWTYPE_ADD ||
			$this->RowType == EW_ROWTYPE_EDIT ||
			$this->RowType == EW_ROWTYPE_SEARCH) { // Add / Edit / Search row
			$this->SetupFieldTitles();
		}

		// Call Row Rendered event
		if ($this->RowType <> EW_ROWTYPE_AGGREGATEINIT)
			$this->Row_Rendered();
	}

	// Validate form
	function ValidateForm() {
		global $Language, $gsFormError;

		// Initialize form error message
		$gsFormError = "";

		// Check if validation required
		if (!EW_SERVER_VALIDATE)
			return ($gsFormError == "");
		if (!$this->Active->FldIsDetailKey && !is_null($this->Active->FormValue) && $this->Active->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->Active->FldCaption());
		}
		if (!ew_CheckInteger($this->Active->FormValue)) {
			ew_AddMessage($gsFormError, $this->Active->FldErrMsg());
		}

		// Return validate result
		$ValidateForm = ($gsFormError == "");

		// Call Form_CustomValidate event
		$sFormCustomError = "";
		$ValidateForm = $ValidateForm && $this->Form_CustomValidate($sFormCustomError);
		if ($sFormCustomError <> "") {
			ew_AddMessage($gsFormError, $sFormCustomError);
		}
		return $ValidateForm;
	}

	// Add record
	function AddRow($rsold = NULL) {
		global $conn, $Language, $Security;

		// Load db values from rsold
		if ($rsold) {
			$this->LoadDbValues($rsold);
		}
		$rsnew = array();

		// CardId
		$this->CardId->SetDbValueDef($rsnew, $this->CardId->CurrentValue, NULL, FALSE);

		// Name
		$this->Name->SetDbValueDef($rsnew, $this->Name->CurrentValue, NULL, FALSE);

		// Surname
		$this->Surname->SetDbValueDef($rsnew, $this->Surname->CurrentValue, NULL, FALSE);

		// Phone
		$this->Phone->SetDbValueDef($rsnew, $this->Phone->CurrentValue, NULL, FALSE);

		// Active
		$this->Active->SetDbValueDef($rsnew, $this->Active->CurrentValue, 0, strval($this->Active->CurrentValue) == "");

		// Call Row Inserting event
		$rs = ($rsold == NULL) ? NULL : $rsold->fields;
		$bInsertRow = $this->Row_Inserting($rs, $rsnew);
		if ($bInsertRow) {
			$conn->raiseErrorFn = 'ew_ErrorFn';
			$AddRow = $this->Insert($rsnew);
			$conn->raiseErrorFn = '';
			if ($AddRow) {
			}
		} else {
			if ($this->getSuccessMessage() <> "" || $this->getFailureMessage() <> "") {

				// Use the message, do nothing
			} elseif ($this->CancelMessage <> "") {
				$this->setFailureMessage($this->CancelMessage);
				$this->CancelMessage = "";
			} else {
				$this->setFailureMessage($Language->Phrase("InsertCancelled"));
			}
			$AddRow = FALSE;
		}

		// Get insert id if necessary
		if ($AddRow) {
			$this->id->setDbValue($conn->Insert_ID());
			$rsnew['id'] = $this->id->DbValue;
		}
		if ($AddRow) {

			// Call Row Inserted event
			$rs = ($rsold == NULL) ? NULL : $rsold->fields;
			$this->Row_Inserted($rs, $rsnew);
		}
		return $AddRow;
	}

	// Set up Breadcrumb
	function SetupBreadcrumb() {
		global $Breadcrumb, $Language;
		$Breadcrumb = new cBreadcrumb();
		$Breadcrumb->Add("list", $this->TableVar, "Cardslist.php", $this->TableVar, TRUE);
		$PageId = ($this->CurrentAction == "C") ? "Copy" : "Add";
		$Breadcrumb->Add("add", $PageId, ew_CurrentUrl());
	}

	// Page Load event
	function Page_Load() {

		//echo "Page Load";
	}

	// Page Unload event
	function Page_Unload() {

		//echo "Page Unload";
	}

	// Page Redirecting event
	function Page_Redirecting(&$url) {

		// Example:
		//$url = "your URL";

	}

	// Message Showing event
	// $type = ''|'success'|'failure'|'warning'
	function Message_Showing(&$msg, $type) {
		if ($type == 'success') {

			//$msg = "your success message";
		} elseif ($type == 'failure') {

			//$msg = "your failure message";
		} elseif ($type == 'warning') {

			//$msg = "your warning message";
		} else {

			//$msg = "your message";
		}
	}

	// Page Render event
	function Page_Render() {

		//echo "Page Render";
	}

	// Page Data Rendering event
	function Page_DataRendering(&$header) {

		// Example:
		//$header = "your header";

	}

	// Page Data Rendered event
	function Page_DataRendered(&$footer) {

		// Example:
		//$footer = "your footer";

	}

	// Form Custom Validate event
	function Form_CustomValidate(&$CustomError) {

		// Return error message in CustomError
		return TRUE;
	}
}
?>
<?php ew_Header(FALSE) ?>
<?php

// Create page object
if (!isset($Cards_add)) $Cards_add = new cCards_add();

// Page init
$Cards_add->Page_Init();

// Page main
$Cards_add->Page_Main();

// Global Page Rendering event (in userfn*.php)
Page_Rendering();

// Page Rendering event
$Cards_add->Page_Render();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Page object
var Cards_add = new ew_Page("Cards_add");
Cards_add.PageID = "add"; // Page ID
var EW_PAGE_ID = Cards_add.PageID; // For backward compatibility

// Form object
var fCardsadd = new ew_Form("fCardsadd");

// Validate form
fCardsadd.Validate = function() {
	if (!this.ValidateRequired)
		return true; // Ignore validation
	var $ = jQuery, fobj = this.GetForm(), $fobj = $(fobj);
	this.PostAutoSuggest();
	if ($fobj.find("#a_confirm").val() == "F")
		return true;
	var elm, felm, uelm, addcnt = 0;
	var $k = $fobj.find("#" + this.FormKeyCountName); // Get key_count
	var rowcnt = ($k[0]) ? parseInt($k.val(), 10) : 1;
	var startcnt = (rowcnt == 0) ? 0 : 1; // Check rowcnt == 0 => Inline-Add
	var gridinsert = $fobj.find("#a_list").val() == "gridinsert";
	for (var i = startcnt; i <= rowcnt; i++) {
		var infix = ($k[0]) ? String(i) : "";
		$fobj.data("rowindex", infix);
			elm = this.GetElements("x" + infix + "_Active");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($Cards->Active->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_Active");
			if (elm && !ew_CheckInteger(elm.value))
				return this.OnError(elm, "<?php echo ew_JsEncode2($Cards->Active->FldErrMsg()) ?>");

			// Set up row object
			ew_ElementsToRow(fobj);

			// Fire Form_CustomValidate event
			if (!this.Form_CustomValidate(fobj))
				return false;
	}

	// Process detail forms
	var dfs = $fobj.find("input[name='detailpage']").get();
	for (var i = 0; i < dfs.length; i++) {
		var df = dfs[i], val = df.value;
		if (val && ewForms[val])
			if (!ewForms[val].Validate())
				return false;
	}
	return true;
}

// Form_CustomValidate event
fCardsadd.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
fCardsadd.ValidateRequired = true;
<?php } else { ?>
fCardsadd.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
// Form object for search

</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php $Breadcrumb->Render(); ?>
<?php $Cards_add->ShowPageHeader(); ?>
<?php
$Cards_add->ShowMessage();
?>
<form name="fCardsadd" id="fCardsadd" class="ewForm form-horizontal" action="<?php echo ew_CurrentPage() ?>" method="post">
<input type="hidden" name="t" value="Cards">
<input type="hidden" name="a_add" id="a_add" value="A">
<table class="ewGrid"><tr><td>
<table id="tbl_Cardsadd" class="table table-bordered table-striped">
<?php if ($Cards->CardId->Visible) { // CardId ?>
	<tr id="r_CardId">
		<td><span id="elh_Cards_CardId"><?php echo $Cards->CardId->FldCaption() ?></span></td>
		<td<?php echo $Cards->CardId->CellAttributes() ?>>
<span id="el_Cards_CardId" class="control-group">
<input type="text" data-field="x_CardId" name="x_CardId" id="x_CardId" size="30" maxlength="50" placeholder="<?php echo $Cards->CardId->PlaceHolder ?>" value="<?php echo $Cards->CardId->EditValue ?>"<?php echo $Cards->CardId->EditAttributes() ?>>
</span>
<?php echo $Cards->CardId->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($Cards->Name->Visible) { // Name ?>
	<tr id="r_Name">
		<td><span id="elh_Cards_Name"><?php echo $Cards->Name->FldCaption() ?></span></td>
		<td<?php echo $Cards->Name->CellAttributes() ?>>
<span id="el_Cards_Name" class="control-group">
<input type="text" data-field="x_Name" name="x_Name" id="x_Name" size="30" maxlength="50" placeholder="<?php echo $Cards->Name->PlaceHolder ?>" value="<?php echo $Cards->Name->EditValue ?>"<?php echo $Cards->Name->EditAttributes() ?>>
</span>
<?php echo $Cards->Name->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($Cards->Surname->Visible) { // Surname ?>
	<tr id="r_Surname">
		<td><span id="elh_Cards_Surname"><?php echo $Cards->Surname->FldCaption() ?></span></td>
		<td<?php echo $Cards->Surname->CellAttributes() ?>>
<span id="el_Cards_Surname" class="control-group">
<input type="text" data-field="x_Surname" name="x_Surname" id="x_Surname" size="30" maxlength="50" placeholder="<?php echo $Cards->Surname->PlaceHolder ?>" value="<?php echo $Cards->Surname->EditValue ?>"<?php echo $Cards->Surname->EditAttributes() ?>>
</span>
<?php echo $Cards->Surname->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($Cards->Phone->Visible) { // Phone ?>
	<tr id="r_Phone">
		<td><span id="elh_Cards_Phone"><?php echo $Cards->Phone->FldCaption() ?></span></td>
		<td<?php echo $Cards->Phone->CellAttributes() ?>>
<span id="el_Cards_Phone" class="control-group">
<input type="text" data-field="x_Phone" name="x_Phone" id="x_Phone" size="30" maxlength="20" placeholder="<?php echo $Cards->Phone->PlaceHolder ?>" value="<?php echo $Cards->Phone->EditValue ?>"<?php echo $Cards->Phone->EditAttributes() ?>>
</span>
<?php echo $Cards->Phone->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($Cards->Active->Visible) { // Active ?>
	<tr id="r_Active">
		<td><span id="elh_Cards_Active"><?php echo $Cards->Active->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $Cards->Active->CellAttributes() ?>>
<span id="el_Cards_Active" class="control-group">
<input type="text" data-field="x_Active" name="x_Active" id="x_Active" size="30" placeholder="<?php echo $Cards->Active->PlaceHolder ?>" value="<?php echo $Cards->Active->EditValue ?>"<?php echo $Cards->Active->EditAttributes() ?>>
</span>
<?php echo $Cards->Active->CustomMsg ?></td>
	</tr>
<?php } ?>
</table>
</td></tr></table>
<button class="btn btn-primary ewButton" name="btnAction" id="btnAction" type="submit"><?php echo $Language->Phrase("AddBtn") ?></button>
</form>
<script type="text/javascript">
fCardsadd.Init();
<?php if (EW_MOBILE_REFLOW && ew_IsMobile()) { ?>
ew_Reflow();
<?php } ?>
</script>
<?php
$Cards_add->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$Cards_add->Page_Terminate();
?>
