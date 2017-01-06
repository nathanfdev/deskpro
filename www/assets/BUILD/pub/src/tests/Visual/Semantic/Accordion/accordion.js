import React from 'react';
import { storiesOf } from '@kadira/storybook';
import { Accordion } from 'Semantic/Accordion';
import { List } from 'Semantic/List';

const structureSimple = {
  panels: [
    {
      title:   'What is a dog?',
      content: 'A dog is a type of domesticated animal. Known for its loyalty and faithfulness, it can be found as' +
      ' a welcome guest in many households across the world.'
    },
    {
      title:   'What kinds of dogs are there?',
      content: 'There are many breeds of dogs. Each breed varies in size and temperament. Owners often select a' +
      ' breed of dog that they find to be compatible with their own lifestyle and desires from a companion.'
    },
    {
      title:   'How do you acquire a dog?',
      content: 'Three common ways for a prospective owner to acquire a dog is from pet shops, private owners, or' +
      ' shelters.'
    }
  ]
};
const structureNodes = {
  panels: [
    {
      title:   'What is a dog?',
      content: <p>A dog is a type of domesticated animal. Known for its loyalty and faithfulness, it can be found
      as a welcome guest in many households across the world.</p>
    },
    {
      title:   'What kinds of dogs are there?',
      content: <p>There are many breeds of dogs. Each breed varies in size and temperament. Owners often select a
      breed of dog that they find to be compatible with their own lifestyle and desires from a companion.</p>
    },
    {
      title:   'How do you acquire a dog?',
      content: [
        <p>
          Three common ways for a prospective owner to acquire a dog is from pet shops, private owners, or shelters.
        </p>,
        <p>
          A pet shop may be the most convenient way to buy a dog. Buying a dog from a private owner allows you to
          assess the pedigree and upbringing of your dog before choosing to take it home. Lastly, finding your dog
          from a shelter, helps give a good home to a dog who may not find one so readily.
        </p>
      ]
    }
  ]
};
const validationList = {
  elements: [
    {
      label: 'New ticket (validation required)',
      icon:  'code'
    },
    {
      label: 'Ticket new Validate email',
      icon:  'code'
    }
  ]
};
const listStructure = {
  panels: [
    {
      title:   'Validation',
      icon:    'open folder',
      count:   validationList.elements,
      content: <List {...validationList} />
    }
  ]
};

storiesOf('Semantic: accordion', module)
  .add(
    'Accordion simple',
    () => <Accordion {...structureSimple} />
  )
  .add(
    'Accordion nodes',
    () => <Accordion {...structureNodes} />
  )
  .add(
    'Accordion list',
    () => <Accordion {...listStructure} />
  )
;

