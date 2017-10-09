import React, { PropTypes } from 'react';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import { Input, Form, Field } from 'DeskPRO/Component/Semantic/ReactForm';
import { Fieldset, createValue } from 'react-forms';


class Edit extends BaseForm {

  static propTypes = {
    report: PropTypes.object
  };

  getDefaultState() {
    const report = this.props.report;

    return {
      title:   report.get('title'),
      select:  report.get('query_parts') ? report.get('query_parts').get('select') : '',
      from:    report.get('query_parts') ? report.get('query_parts').get('from') : '',
      where:   report.get('query_parts') ? report.get('query_parts').get('where') : '',
      splitBy: report.get('query_parts') ? report.get('query_parts').get('splitBy') : '',
      groupBy: report.get('query_parts') ? report.get('query_parts').get('groupBy') : '',
      orderBy: report.get('query_parts') ? report.get('query_parts').get('orderBy') : '',
      offset:  report.get('query_parts') ? report.get('query_parts').get('offset') : '',
      limit:   report.get('query_parts') ? report.get('query_parts').get('limit') : '',
    };
  }

  componentWillReceiveProps(props) {
    const { report } = props;

    if (props.report) {
      this.setState({
        formData: createValue({
          value: {
            title:   report.get('title'),
            select:  report.get('query_parts') ? report.get('query_parts').get('select') : '',
            from:    report.get('query_parts') ? report.get('query_parts').get('from') : '',
            where:   report.get('query_parts') ? report.get('query_parts').get('where') : '',
            splitBy: report.get('query_parts') ? report.get('query_parts').get('splitBy') : '',
            groupBy: report.get('query_parts') ? report.get('query_parts').get('groupBy') : '',
            orderBy: report.get('query_parts') ? report.get('query_parts').get('orderBy') : '',
            offset:  report.get('query_parts') ? report.get('query_parts').get('offset') : '',
            limit:   report.get('query_parts') ? report.get('query_parts').get('limit') : '',
          },
          errorList: {},
          onChange:  this.onChange
        })
      });
    }
  }

  renderReport() {
    return (
      <div className="reports-editor-panel full-editor">
        <Form formValue={this.state.formData} className="editor-form full-editor-form">
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

            <div className="editor-controls">
              <a className="button">Save Query <i className="fa fa-save" /></a>
              <a className="button button-edit">Test <i className="fa fa-fast-forward" /></a>
            </div>
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
