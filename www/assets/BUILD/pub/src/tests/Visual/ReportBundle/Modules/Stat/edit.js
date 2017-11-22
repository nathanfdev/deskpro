import React from 'react';
import { createStore, combineReducers } from 'redux';
import { Provider } from 'react-redux';
import { reduxForm, reducer as formReducer } from 'redux-form';
import { EditForm } from 'DeskPRO/Bundle/ReportBundle/Modules/Stats/Components/EditForm';
import { storiesOf } from '@kadira/storybook';
import { reportCss } from '../../../decorators';
import { groupParams } from './sampleGroupParams';

const store = createStore(combineReducers({
  form: formReducer
}));

storiesOf('Reports: Stat', module)
  .addDecorator(story => reportCss(story()))
  .addDecorator((story) => (<Provider store={store}>{story()}</Provider>))
  .add(
    'Edit form',
    () => {
      const Form = reduxForm({
        form: 'test'
      })(EditForm);

      return <Form groupParams={groupParams} />;
    }
  )
;
