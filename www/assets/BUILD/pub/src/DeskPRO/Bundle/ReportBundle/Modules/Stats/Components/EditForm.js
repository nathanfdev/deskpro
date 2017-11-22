import React from 'react';
import PropTypes from 'prop-types';
import { Button, Container, Tabs, TabLink, Section } from '@deskpro/react-components';
import { Form, Input, Checkbox, Textarea, Select, validators } from '@deskpro/react-components/lib/bindings/redux-form';
import { formValues, FieldArray } from 'redux-form';
import classNames from 'classnames';

class VarsFieldComponent extends React.Component {

  static propTypes = {
    groupParams: PropTypes.object,
    fields: PropTypes.object,
    vars: PropTypes.array
  };

  static types = [
    {
      label: 'Date',
      value: 'dates',
    },
    {
      label: 'Status',
      value: 'statuses',
    },
    {
      label: 'Field',
      value: 'fields',
    },
    {
      label: 'Order',
      value: 'orders',
    },
  ];

  static validateVarName(name) {
    if (!name || name.length === 0) {
      return undefined;
    }
    if (!name.match(/^[A-Za-z0-9_]+$/)) {
      return 'Only letters, numbers and underscores are allowed.';
    }
    return undefined;
  }

  renderDateField(name, dates) {
    if (!dates) {
      return null;
    }

    const choices = Object.entries(dates).map(([index, date]) => {
      const choice = { value: index, label: date[0] };
      return choice;
    });

    return (<Select key={name} name={name} options={choices} />);
  }

  renderTypeField(name, values) {
    if (!values) {
      return null;
    }

    const choices = Object.keys(values).map((value) => {
      const choice = { label: value, value };
      return choice;
    });

    return (<Select label="Record Type" key={name} name={name} options={choices} />);
  }

  renderTypeValueField(name, values) {
    if (!values) {
      return null;
    }

    const choices = Object.entries(values).map(([key, value]) => {
      const choice = { label: value[0], value: key };
      return choice;
    });

    return (<Select label="Default Value" key={name} name={name} options={choices} />);
  }

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
                options={VarsFieldComponent.types}
                name={`${varName}.type`}
              />
              { variable.type === 'dates' && this.renderDateField(`${varName}.field_value`, groupParams.dates) }
              { variable.type && variable.type !== 'dates' && groupParams[variable.type] && [
                this.renderTypeField(`${varName}.field_type`, groupParams[variable.type]),
                variable.field_type && groupParams[variable.type][variable.field_type] && this.renderTypeValueField(`${varName}.field_value`, groupParams[variable.type][variable.field_type])
              ] }
            </div>);
          })}
        </div>
        <Button onClick={() => fields.push() }>Add Variable</Button>
      </div>
    );
  }
}

const VarsField = formValues('vars')(VarsFieldComponent);

export class EditFormComponent extends React.Component {

  static propTypes = {
    groupParams:  PropTypes.object,
    select: PropTypes.string
  };

  constructor(props) {
    super(props);
    this.state = {
      queryInputMode: 'form'
    };
  }

  queryModeChange = (to) => {
    this.setState({ queryInputMode: to });
  }

  render() {
    const select = this.props.select;

    const renderVars = (field) => {
      return (
        <VarsField fields={field.fields} groupParams={this.props.groupParams || {}} />
      );
    };

    return (
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
            <Section hidden={this.state.queryInputMode !== 'form'}>
              <Input
                label="SELECT"
                id="query_select"
                name="query_select"
              />
              <div className={classNames({ 'field-hidden': !(select && select.match(/count\s*\(.*?\)/i)) })}>
                <Checkbox
                  label="WITH COUNT ROLLUP - Adds a Total column to COUNT() queries made against hierarchies"
                  id="query_with_rollup"
                  name="query_with_rollup"
                />
              </div>
              <Input
                label="FROM"
                id="query_from"
                name="query_from"
              />
              <Input
                label="WHERE"
                id="query_where"
                name="query_where"
              />
              <Input
                label="SPLIT BY"
                id="query_split_by"
                name="query_split_by"
              />
              <Input
                label="GROUP BY"
                id="query_group_by"
                name="query_group_by"
              />
              <div style={{width: '150px'}}>
                <Input
                  label="LIMIT"
                  id="query_limit"
                  name="query_limit"
                />
                <Input
                  label="OFFSET"
                  id="query_offset"
                  name="query_offset"
                />
              </div>
            </Section>
            <Section hidden={this.state.queryInputMode !== 'dpql'}>
              <Textarea
                id="query_raw"
                name="query_raw"
              />
            </Section>
            <div className="vars-wrap">
              <Section>
                <FieldArray name="vars" component={renderVars} />
              </Section>
            </div>
          </div>
        </div>
      </Container>
    );
  }
}

export const EditForm = formValues({ select: 'query_select' })(EditFormComponent);