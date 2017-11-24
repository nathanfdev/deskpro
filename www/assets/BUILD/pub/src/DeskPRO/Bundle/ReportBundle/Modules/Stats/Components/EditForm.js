import React from 'react';
import PropTypes from 'prop-types';
import { Button, Container, Tabs, TabLink, Section } from '@deskpro/react-components';
import { Input, Checkbox, Textarea, Select, validators } from '@deskpro/react-components/lib/bindings/redux-form';
import { formValues, Field, FieldArray, FormSection } from 'redux-form';
import classNames from 'classnames';
import { displayTypes } from './helper';

class VarsFieldComponent extends React.PureComponent {

  static propTypes = {
    groupParams: PropTypes.object,
    fields:      PropTypes.object,
    vars:        PropTypes.array
  };

  static validateVarName(name) {
    if (!name || name.length === 0) {
      return undefined;
    }
    if (!name.match(/^[A-Za-z0-9_]+$/)) {
      return 'Only letters, numbers and underscores are allowed.';
    }
    return undefined;
  }

  static renderDateField(name, dates) {
    if (!dates) {
      return null;
    }

    const choices = Object.entries(dates).map(([index, date]) => {
      const choice = { value: index, label: date[0] };
      return choice;
    });

    return (<Select key={name} name={name} options={choices} />);
  }

  static renderTypeField(name, values) {
    if (!values) {
      return null;
    }

    const choices = Object.keys(values).map((value) => {
      const choice = { label: value, value };
      return choice;
    });

    return (<Select label="Record Type" key={name} name={name} options={choices} />);
  }

  static renderTypeValueField(name, values) {
    if (!values) {
      return null;
    }

    const choices = Object.entries(values).map(([key, value]) => {
      const choice = { label: value[0], value: key };
      return choice;
    });

    return (<Select label="Default Value" key={name} name={name} options={choices} />);
  }

  onAddButtonClick = (event) => {
    event.preventDefault();
    event.stopPropagation();
    this.props.fields.push();
  };

  render() {
    const { fields, groupParams, vars } = this.props;

    return (
      <div className="varsfield-wrap">
        <div className="varsfield-list">
          {fields.map((varName, index) => {
            const variable = vars && vars[index] ? vars[index] : {};

            let hint;
            if (!variable.name) {
              hint = 'ID';
            } else {
              hint = (<span>ID as <em>{'${'}{variable.name}{'}'}</em></span>);
            }

            return (<div className="varsfield-item" key={index}>
              <div className="remove-ctrl" onClick={() => fields.remove(index)}><i className="fa fa-trash" /></div>
              <Input
                label={hint}
                name={`${varName}.name`}
                validate={[VarsFieldComponent.validateVarName]}
              />
              <Select
                label="Type"
                options={displayTypes}
                name={`${varName}.type`}
              />
              { variable.type === 'dates' && VarsFieldComponent.renderDateField(`${varName}.field_value`, groupParams.dates) }
              { variable.type && variable.type !== 'dates' && groupParams[variable.type] && [
                VarsFieldComponent.renderTypeField(`${varName}.field_type`, groupParams[variable.type]),
                variable.field_type && groupParams[variable.type][variable.field_type] && VarsFieldComponent.renderTypeValueField(`${varName}.field_value`, groupParams[variable.type][variable.field_type])
              ] }
            </div>);
          })}
        </div>
        <Button onClick={this.onAddButtonClick} type="secondary" size="medium">Add Variable</Button>
      </div>
    );
  }
}

const VarsField = formValues('vars')(VarsFieldComponent);

export class EditFormComponent extends React.PureComponent {

  static propTypes = {
    groupParams:  PropTypes.object,
    select:       PropTypes.string,
    groupBy:      PropTypes.string,
    dpqlParser:   PropTypes.func,
    change:       PropTypes.func,
    queryValues:  PropTypes.object,
    handleSubmit: PropTypes.func,
    error:        PropTypes.string,
  };

  static toDpql(fields) {
    const parts = [];
    parts.push(`SELECT ${fields.select || 'COUNT(*)'}`);
    parts.push(`FROM ${fields.from || '???'}`);
    if (fields.where) {
      parts.push(`WHERE ${fields.where}`);
    }
    if (fields.splitBy) {
      parts.push(`SPLIT BY ${fields.splitBy}`);
    }
    if (fields.groupBy) {
      parts.push(`GROUP BY ${fields.groupBy}`);
      if (fields.with_rollup) {
        parts.push('WITH ROLLUP');
      }
    }
    if (fields.limit && fields.offset) {
      parts.push(`LIMIT ${fields.limit} OFFSET ${fields.offset}`);
    } else if (fields.limit) {
      parts.push(`LIMIT ${fields.limit}`);
    } else if (fields.offset) {
      parts.push(`LIMIT 1000, ${fields.offset}`);
    }
    return parts.join('\n');
  }

  constructor(props) {
    super(props);
    this.state = {
      queryInputMode:    'form',
      queryModeChanging: true
    };
  }

  componentDidMount() {
    this.props.change('query_input_mode', this.state.queryInputMode);
  }

  shouldComponentUpdate(nextProps, nextState) {
    for (const k of Object.keys(nextProps)) {
      if (k !== 'queryValues' && nextProps[k] !== this.props[k]) {
        return true;
      }
    }
    for (const k of Object.keys(nextState)) {
      if (nextState[k] !== this.state[k]) {
        return true;
      }
    }
    return false;
  }

  queryModeChange = (to) => {
    if (to === 'dpql') {
      const dpql = EditFormComponent.toDpql(this.props.queryValues || {});
      this.setState({ queryInputMode: to, queryModeChanging: false, origDpql: dpql });
      this.props.change('query.raw', dpql);
    } else if (this.props.queryValues.raw === this.state.origDpql) {
      this.setState({ queryInputMode: to, queryModeChanging: false });
    } else {
      this.setState({ queryInputMode: to, queryModeChanging: true });
      if (this.props.dpqlParser) {
        this.setState({ queryInputMode: to, queryModeChanging: true });
        this.props.dpqlParser(this.props.queryValues.raw).then((fields) => {
          if (this.state.queryInputMode === 'form') {
            this.setState({ queryModeChanging: false });
            Object.keys(fields).forEach((f) => {
              this.props.change(`query.${f}`, fields[f]);
            });
          }
        });
      } else {
        this.setState({ queryInputMode: to, queryModeChanging: false });
      }
    }
  }

  render() {
    const select  = this.props.select;
    const groupBy = this.props.groupBy || '';

    const renderVars = field => <VarsField fields={field.fields} groupParams={this.props.groupParams || {}} />;

    return (
      <form onSubmit={this.props.handleSubmit}>
        <Container>
          <Input
            label="Title"
            id="title"
            name="title"
            validate={validators.required}
          />
          <Input
            label="Labels"
            id="labels"
            name="labels"
          />
          <Field component="input" type="hidden" name="query_input_mode" />
          <div className="query-builder-input">
            <Tabs active={this.state.queryInputMode} onChange={this.queryModeChange}>
              <TabLink name="form">
                Query Builder
              </TabLink>
              <TabLink name="dpql">
                Raw DPQL
              </TabLink>
            </Tabs>
            <div className="input-wrap">
              <FormSection name="query">
                <Section hidden={this.state.queryInputMode !== 'form'}>
                  <Input
                    label="SELECT"
                    name="select"
                  />
                  <Input
                    label="FROM"
                    name="from"
                  />
                  <Input
                    label="WHERE"
                    name="where"
                  />
                  <Input
                    label="SPLIT BY"
                    name="splitBy"
                  />
                  <Input
                    label="GROUP BY"
                    name="groupBy"
                  />
                  <div className={classNames({ 'field-hidden': !(select && select.match(/count\s*\(.*?\)/i) && groupBy.length) })}>
                    <Checkbox
                      label="WITH ROLLUP - Adds a Total column to grouped COUNT queries made against hierarchies"
                      name="with_rollup"
                    />
                  </div>
                  <div style={{ width: '150px' }}>
                    <Input
                      label="LIMIT"
                      name="limit"
                    />
                    <Input
                      label="OFFSET"
                      name="offset"
                    />
                  </div>
                </Section>
                <Section hidden={this.state.queryInputMode !== 'dpql'}>
                  <Textarea
                    name="raw"
                  />
                </Section>
              </FormSection>
              <div className="vars-wrap">
                <Section>
                  <FieldArray name="vars" component={renderVars} />
                </Section>
              </div>
            </div>
          </div>
        </Container>
        {this.props.error && <div className="form-error-message">{this.props.error}</div>}
      </form>
    );
  }
}

export const EditForm = formValues({
  queryValues: 'query',
  select:      'query.select',
  groupBy:     'query.groupBy'
})(EditFormComponent);
