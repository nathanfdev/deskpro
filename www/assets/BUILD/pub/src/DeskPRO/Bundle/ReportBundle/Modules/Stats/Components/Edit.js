import PropTypes from 'prop-types';
import React from 'react';
import { reduxForm, submit, SubmissionError } from 'redux-form';
import { Button, Loader } from '@deskpro/react-components';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import Immutable from 'immutable';
import classNames from 'classnames';
import { connect } from 'react-redux';
import { EditForm } from './EditForm';
import { saveReport } from '../../Application/Actions/reportActions';
import { transformReportDataToApi } from './helper';

class EditContainer extends React.Component {

  static propTypes = {
    report:       PropTypes.object.isRequired,
    groupParams:  PropTypes.object.isRequired,
    labels:       PropTypes.object.isRequired,
    onCloneClick: PropTypes.func.isRequired,
    onRunClick:   PropTypes.func.isRequired,
    dispatch:     PropTypes.func.isRequired,
    onSubmit:     PropTypes.func.isRequired
  };

  static dpqlParser(query) {
    const data = {
      query,
      current_type: 'query',
      new_type:     'builder'
    };

    return api.sendPost('DP_API/report_widgets/parse', data).then(res => res.data.parts);
  }

  static getStateFromReport(report) {
    const queryParts = report.has('query_parts') ? report.get('query_parts') : Immutable.fromJS({});
    const initialFormValue = {
      title:  report.get('title'),
      labels: report.get('labels', Immutable.List()).toArray(),
      query:  {
        raw:      report.get('query'),
        select:   queryParts.get('select', ''),
        from:     queryParts.get('from', ''),
        where:    queryParts.get('where', ''),
        split_by: queryParts.get('split_by', ''),
        group_by: queryParts.get('group_by', ''),
        order_by: queryParts.get('order_by', ''),
        offset:   queryParts.get('offset', ''),
        limit:    queryParts.get('limit', '')
      },
      vars: report.get('variables', Immutable.Map()).toJS(),
    };

    return { initialFormValue };
  }

  constructor(props) {
    super(props);
    this.state = {
      saving:     false,
      error:      false,
      formErrors: {},
      ...EditContainer.getStateFromReport(props.report)
    };
  }

  componentWillReceiveProps(props) {
    if (props.report !== this.props.report) {
      this.setState({
        saving:     false,
        error:      false,
        formErrors: {},
        ...EditContainer.getStateFromReport(props.report)
      });
    }
  }

  onCloneClick = (event) => {
    event.preventDefault();
    event.stopPropagation();
    this.props.onCloneClick(this.props.report);
  };

  onRunClick = (event) => {
    event.preventDefault();
    event.stopPropagation();
    this.props.onRunClick(this.props.report);
  };

  onSubmit = (formData) => {
    const { dispatch, report } = this.props;

    const labels        = formData.labels.length ? formData.labels : [];
    const displayTypes  = report.get('display_types', Immutable.fromJS([])).toArray().map(t => t.toLowerCase());

    const reportData = {
      id:            this.props.report.get('id') || null,
      title:         formData.title,
      display_types: displayTypes.length ? displayTypes : ['table'],
      vars:          formData.vars,
      inputMode:     formData.query_input_mode,
      labels,
      ...formData.query
    };

    this.setState({
      saving:     true,
      error:      false,
      formErrors: {}
    });

    dispatch(saveReport(reportData))
        .then(() => {
          const current = transformReportDataToApi(reportData);
          current.id = reportData.id;
          current.is_custom = true;
          current.extended_query = reportData.raw ? reportData.raw.indexOf('LAYER WITH') !== -1 : false;
          this.setState({
            saving:     false,
            error:      false,
            formErrors: {}
          }, () => this.props.onSubmit(current));
        })
        .catch((response) => {
          const flattenErrors = {};
          if (response.data.errors) {
            Object.keys(response.data.errors.fields).forEach((key) => {
              flattenErrors[key] = response.data.errors.fields[key].errors.map(error => error.message).join(' ');
            });
          }

          const data = transformReportDataToApi(reportData, true);
          if (reportData.id > 0) {
            data.id = reportData.id;
          }

          // return state of EditForm to one which has errors in dpql
          this.setState({ saving: false, error: true, formErrors: flattenErrors, ...EditContainer.getStateFromReport(Immutable.fromJS(data)) });
          throw new SubmissionError(flattenErrors);
        });
  };

  doSubmit = () => {
    this.props.dispatch(submit('editStat'));
  };

  renderForm() {
    const { initialFormValue, saving, error, formErrors } = this.state;
    const { groupParams, report, labels } = this.props;

    const EditStatForm = reduxForm({
      form:               'editStat',
      initialValues:      initialFormValue,
      onSubmit:           this.onSubmit,
      enableReinitialize: true
    })(EditForm);

    const saveBtn = (<button
      type="button"
      onClick={this.doSubmit}
      className={classNames(
        'dp-button',
        'dp-button--l',
        'dp-button--primary',
        'dp-button--shape-default',
        { 'dp-button--loading': saving }
      )}
    >Save</button>);

    const controls = (
      <div style={{ textAlign: 'center', margin: '15px' }}>
        { report.get('is_custom')
          ? saveBtn
          : <em>You cannot edit a built-in stat. If you want to change it, click the clone button above.</em> }
      </div>
    );

    return (<div>
      <EditStatForm
        hasError={error}
        formErrors={formErrors}
        labels={labels.toJS()}
        groupParams={groupParams.toJS()}
        dpqlParser={EditContainer.dpqlParser}
      />
      {controls}
    </div>);
  }

  renderReport() {
    return (
      <div className="report-view edit">
        { this.props.report.get('id') ?
          <div className="title-bar">
            <div className="title">{this.props.report.get('title')}</div>
            <div className="ctrl">
              <Button type="secondary" size="medium" onClick={this.onRunClick}>
                <i className="fa fa-undo" /> Cancel
              </Button>
              <Button size="medium" onClick={this.onCloneClick}><i className="fa fa-clone" /> Clone</Button>
            </div>
          </div>
          : <div className="title-bar"><div className="title">New Stat</div></div> }
        {this.renderForm()}
      </div>
    );
  }

  render() {
    const { report } = this.props;
    const reportExists  = report && report.get('id') && report.get('query_parts');
    return (
      <div className="report-widget-edit-n-run-content">
        { (reportExists || report.get('is_new')) ? this.renderReport() : <Loader size="xlarge" /> }
      </div>
    );
  }
}

export const Edit = connect()(EditContainer);
