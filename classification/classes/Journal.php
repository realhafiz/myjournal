<?php
require_once 'BaseClass.php';
require_once dirname(__FILE__).'/../models/JournalModel.php';
require_once dirname(__FILE__).'/../models/CriteriaModel.php';
require_once dirname(__FILE__).'/../models/FormModel.php';

class Journal extends BaseClass
{
	public $journalModel;
	public $limit = 100;

	function __construct() {
		$this->journalModel = new JournalModel();
		$this->criteriaModel = new CriteriaModel();
		$this->formModel = new FormModel();
	}

	function indexAction() {

		// get parameters
		$search = isset($_GET['search']) ? $_GET['search'] : '';
		$page = isset($_GET['page']) ? $_GET['page'] : 1;
		$offset = ($page - 1) * $this->limit;

		// get total records
		$total = $this->journalModel->getCount($search);

		// get list of journals with pagination limit
		$journals = $this->journalModel->getJournals($this->limit, $offset, $search);

		// setup pagination
		$paginator = new SqlPaginator($page, $this->limit, $total);
		$paginator_params = array(
			'search' => $search,
		);
		$paginator->setGetParameters($paginator_params);
		$pagination = $paginator->getPagination();

		// set data to be passed to view
		$data['journals'] = $journals;
		$data['pagination'] = $pagination;
		$data['offset'] = $offset;

		// render view
		$this->render('journal/list', $data);
	}

	function evaluateAction() {

		$id = $_GET['id'];

		// add current evaluation to database
		if (isset($_POST['submitButton'])) {

			// combine arrays
			$criteriaChoices = $_POST['optional'] + $_POST['compulsory'];
			$year = $_POST['year'];
			$remarks = $_POST['remarks'];
			$journalId = $id;

			$result = $this->journalModel->insertEvaluate($journalId, $year, $criteriaChoices, $remarks);

			$_SESSION['success_msg'] = "Evaluation has been successfully created.";
			$this->redirect('classification_evaluated_journals.php');
		}

		// show evaluation form
		$data['journal'] = $this->journalModel->getJournal($id);
		$data['disciplineName'] = $this->journalModel->getDiscipline($data['journal']['discipline_id']);
		$data['compulsory'] = $this->criteriaModel->getCriterias();
		$data['optional'] = $this->criteriaModel->getCriterias(0);

		$this->render('journal/evaluation_form', $data);
	}

	function evaluatedJournalsAction() {

		// get parameters
		$search = isset($_GET['search']) ? $_GET['search'] : '';
		$discipline = isset($_GET['discipline']) ? $_GET['discipline'] : '';
		$page = isset($_GET['page']) ? $_GET['page'] : 1;
		$offset = ($page - 1) * $this->limit;

		// get available forms
		$forms = $this->formModel->getForms();
		$form = $forms[0]['id'];

		// check form parameter
		if (isset($_GET['form'])) {
			$form = $_GET['form'];
		}

		$year = date('Y');
		// check year parameter
		if (isset($_GET['year'])) {
			$year = $_GET['year'];
		}
		else {
			$_GET['year'] = $year;
		}

		// get total records
		$total = $this->journalModel->getEvaluatedCount($search);

		// get list of journals with pagination limit
		$journals = $this->journalModel->getEvaluatedJournals($this->limit, $offset, $search, $form, $year, $discipline);
		$fullMarks = $this->formModel->getTotalMarksForForm($form);

		$offset = 0;
		$pagination = '';

		$disciplines = $this->journalModel->getDiscipline();

		// set data to be passed to view
		$data['journals'] = $journals;
		$data['pagination'] = $pagination;
		$data['offset'] = $offset;
		$data['fullMarks'] = $fullMarks;
		$data['disciplines'] = $disciplines;
		$data['forms'] = $forms;

		// render view
		$this->render('journal/result_list', $data);
	}

	function detailJournal() {
		// list of dropdown forms
		$forms = $this->formModel->getForms();
		$form = $forms[0]['id'];

		// check form parameter
		if (isset($_GET['form']) && $_GET['form'] != '') {
			$form = $_GET['form'];
		}

		// fullmark based on form id
		$fullMarks = $this->formModel->getTotalMarksForForm($form);

		$data['evaluation_id'] = $_GET['evaluation_id'];
		$data['journal'] = $this->journalModel->getEvaluationDetail($_GET['evaluation_id'], $form);
		$data['disciplineTitle'] = $this->journalModel->getDiscipline($data['journal']['discipline_id']);
		$data['forms'] = $forms;
		$data['fullMarks'] = $fullMarks;

		// render view
		$this->render('journal/detail', $data);
	}

	function editEvaluateAction()
	{
		$evaluation_id = $_GET['id'];

		// add current evaluation to database
		if (isset($_POST['submitButton'])) {

			// combine arrays
			$criteriaChoices = $_POST['optional'] + $_POST['compulsory'];
			$year = $_POST['year'];
			$remarks = $_POST['remarks'];

			$result = $this->journalModel->updateEvaluate($evaluation_id, $year, $criteriaChoices, $remarks);

			$_SESSION['success_msg'] = "Evaluation has been successfully updated.";
			$this->redirect('classification_evaluated_journals.php');
		}

		// show evaluation form
		$data['journal'] = $this->journalModel->getEvaluation($evaluation_id);
		$data['disciplineName'] = $this->journalModel->getDiscipline($data['journal']['discipline_id']);
		$data['compulsory'] = $this->criteriaModel->getCriterias();
		$data['optional'] = $this->criteriaModel->getCriterias(0);

		$this->render('journal/edit_evaluation_form', $data);
	}




	function convertListJournal($var) {

		global $counts;
		global $classes;
		global $levels;

		$data['counts'] = $counts;
		$data['classes'] = $classes;
		$data['levels'] = $levels;
		// get parameters
		$search = isset($_GET['s']) ? $_GET['s'] : '';
		//$form = isset($_GET['f']) ? $_GET['f'] : '';

		$discipline = isset($_GET['did']) ? $_GET['did'] : '';
		$page = isset($_GET['page']) ? $_GET['page'] : 1;
		$offset = ($page - 1) * $this->limit;

		// get available forms
		$forms = $this->formModel->getForms();
		$form = $forms[0]['id'];

		// check form parameter
		if (isset($_GET['formID'])) {
			$form = $_GET['formID'];
		}

		$year = date('Y');
		// check year parameter
		if (isset($_GET['y'])) {
			$year = $_GET['y'];
		}
		else {
			$_GET['y'] = $year;
		}

		// get total records
		$total = $this->journalModel->getEvaluatedCount($search);

		// get list of journals with pagination limit
		$journals = $this->journalModel->getEvaluatedJournals($this->limit, $offset, $search, $form, $year, $discipline);
		$fullMarks = $this->formModel->getTotalMarksForForm($form);

		$offset = 0;
		$pagination = '';

		$disciplines = $this->journalModel->getDiscipline();

		// set data to be passed to view
		$data['journals'] = $journals;
		$data['pagination'] = $pagination;
		$data['offset'] = $offset;
		$data['fullMarks'] = $fullMarks;
		$data['disciplines'] = $disciplines;
		$data['forms'] = $forms;

		// render view
		if($var == "EXCEL")
		{
			$this->render('journal/render_list_excel', $data);
		}else{
			$this->render('journal/render_list_pdf', $data);
		}
	}

	/*function toExcel() {

	// get parameters
	$search = isset($_GET['s']) ? $_GET['s'] : '';
	$form = isset($_GET['f']) ? $_GET['f'] : '';

	$discipline = isset($_GET['did']) ? $_GET['did'] : '';
	$page = isset($_GET['page']) ? $_GET['page'] : 1;
	$offset = ($page - 1) * $this->limit;

	// get available forms
	$forms = $this->formModel->getForms();
	$form = $forms[0]['id'];

	$year = date('Y');
	// check year parameter
	if (isset($_GET['y'])) {
	$year = $_GET['y'];
}
else {
$_GET['y'] = $year;
}

// get total records
$total = $this->journalModel->getEvaluatedCount($search);

// get list of journals with pagination limit
$journals = $this->journalModel->getEvaluatedJournals($this->limit, $offset, $search, $form, $year, $discipline);
$fullMarks = $this->formModel->getTotalMarksForForm($form);

$offset = 0;
$pagination = '';

$disciplines = $this->journalModel->getDiscipline();

// set data to be passed to view
$data['journals'] = $journals;
$data['pagination'] = $pagination;
$data['offset'] = $offset;
$data['fullMarks'] = $fullMarks;
$data['disciplines'] = $disciplines;
$data['forms'] = $forms;

//print_r($journals);
// render view

}*/

//$journal->convertDetail("PDF");
function convertDetail($var) {

	// list of dropdown forms
	$forms = $this->formModel->getForms();
	$form = $forms[0]['id'];

	// check form parameter
	if (isset($_GET['fid']) && $_GET['fid'] != '') {
		$form = $_GET['fid'];
	}

	// fullmark based on form id
	$fullMarks = $this->formModel->getTotalMarksForForm($form);

	$data['evaluation_id'] = $_GET['evaluation_id'];
	$data['journal'] = $this->journalModel->getEvaluationDetail($_GET['evaluation_id'], $form);
	$data['disciplineTitle'] = $this->journalModel->getDiscipline($data['journal']['discipline_id']);
	$data['forms'] = $forms;
	$data['fullMarks'] = $fullMarks;

	// render view
	if($var == "EXCEL"){
		$this->render('journal/render_detail_excel',$data);
	}
	else{
		$this->render('journal/render_detail_pdf',$data);
	}
}

function toExcelDetail() {

	// list of dropdown forms
	$forms = $this->formModel->getForms();
	$form = $forms[0]['id'];

	// fullmark based on form id
	$fullMarks = $this->formModel->getTotalMarksForForm($form);

	$data['evaluation_id'] = $_GET['evaluation_id'];
	$data['journal'] = $this->journalModel->getEvaluationDetail($_GET['evaluation_id'], $form);
	$data['disciplineTitle'] = $this->journalModel->getDiscipline($data['journal']['discipline_id']);
	$data['forms'] = $forms;
	$data['fullMarks'] = $fullMarks;

	// render view
	$this->render('journal/render_detail_excel',$data);
}

function deleteEvaluationAction() {
	$this->journalModel->deleteEvaluation();

	$_SESSION['success_msg'] = "Evaluation has been successfully deleted.";
	$this->redirect('classification_evaluated_journals.php');
}
}
