import React, { PropTypes } from 'react';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import { Input, Form, Field } from 'DeskPRO/Component/Semantic/ReactForm';
import { Fieldset, createValue } from 'react-forms';
import classNames from 'classnames';
import VarsField from './Fields/VarsField';


class Edit extends BaseForm {

  static propTypes = {
    report:      PropTypes.object,
    groupParams: PropTypes.object,
    onRunClick:  PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);
    this.onRunClick = this.onRunClick.bind(this);
  }

  getDefaultState() {
    const report = this.props.report;
    const state = {
      title:   report.get('title'),
      desc:    report.has('description') ? report.get('description') : '',
      select:  report.get('query_parts') ? report.get('query_parts').get('select') : '',
      from:    report.get('query_parts') ? report.get('query_parts').get('from') : '',
      where:   report.get('query_parts') ? report.get('query_parts').get('where') : '',
      splitBy: report.get('query_parts') ? report.get('query_parts').get('splitBy') : '',
      groupBy: report.get('query_parts') ? report.get('query_parts').get('groupBy') : '',
      orderBy: report.get('query_parts') ? report.get('query_parts').get('orderBy') : '',
      offset:  report.get('query_parts') ? report.get('query_parts').get('offset') : '',
      limit:   report.get('query_parts') ? report.get('query_parts').get('limit') : '',
      vars:    report.has('variables') ? report.get('variables').toJS() : []
    };

    if (report.get('id')) {
      state.id = report.get('id');
    }

    return state;
  }

  componentWillReceiveProps(props) {
    const { report } = props;
    if (report) {
      const state = {
        value: {
          title:   report.get('title'),
          desc:    report.has('description') ? report.get('description') : '',
          select:  report.get('query_parts') ? report.get('query_parts').get('select') : '',
          from:    report.get('query_parts') ? report.get('query_parts').get('from') : '',
          where:   report.get('query_parts') ? report.get('query_parts').get('where') : '',
          splitBy: report.get('query_parts') ? report.get('query_parts').get('splitBy') : '',
          groupBy: report.get('query_parts') ? report.get('query_parts').get('groupBy') : '',
          orderBy: report.get('query_parts') ? report.get('query_parts').get('orderBy') : '',
          offset:  report.get('query_parts') ? report.get('query_parts').get('offset') : '',
          limit:   report.get('query_parts') ? report.get('query_parts').get('limit') : '',
          vars:    report.get('variables') ? report.get('variables').toJS() : []
        },
        errorList: {},
        onChange:  this.onChange
      };
      if (report.get('id')) {
        state.id = report.get('id');
      }
      this.setState({
        formData: createValue(state)
      });
    }
  }

  onRunClick() {
    this.props.onRunClick(this.props.report);
  }

  renderReport() {
    const { formData, saving } = this.state;

    return (
      <div className="reports-editor-panel full-editor">
        <Form formValue={formData} className="editor-form full-editor-form" onSubmit={this.onSubmit}>
          <Fieldset>
            <Field select="title" label="Title">
              <Input type="text" />
            </Field>
            <Field select="select" label="Select">
              <Input type="text" />
            </Field>
            <Field select="from" label="From">
              <Input type="text" />
            </Field>
            <Field select="where" label="Where">
              <Input type="text" />
            </Field>
            <Field select="splitBy" label="Split By">
              <Input type="text" />
            </Field>
            <Field select="groupBy" label="Group By">
              <Input type="text" />
            </Field>
            <Field select="orderBy" label="Order By">
              <Input type="text" />
            </Field>
            <Field select="limit" label="Limit">
              <Input type="text" />
            </Field>
            <Field select="offset" label="Offset">
              <Input type="text" />
            </Field>

            <Field select="vars" label="Vars" className="vars">
              <VarsField groupParams={this.props.groupParams} />
            </Field>

            <button className={classNames('ui button', { loading: saving })}>Save</button>
            <button onClick={this.onRunClick} className={classNames('ui olive button', { loading: saving })}>Run</button>
          </Fieldset>
        </Form>
      </div>
    );
  }

  render() {
    return (
      <div className="stat-large-preview-wrapper">
        { this.props.report && this.props.report.get('id') && this.props.report.get('query_parts') ? this.renderReport() : '' }
      </div>
    );
  }
}

export default Edit;
