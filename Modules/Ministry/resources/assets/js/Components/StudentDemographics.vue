<template>
    <div v-if="demographics && demographics.length > 0" class="col-12">
        <hr />
        <h5 class="mb-3">Demographics Information</h5>
        
        <div v-for="demographic in demographics" :key="demographic.id" class="mb-3">
            <Label :for="'demographic_' + demographic.id" class="form-label">
                {{ demographic.question }}
                <span v-if="demographic.required" class="text-danger">*</span>
            </Label>
            
            <!-- Text Input -->
            <Input 
                v-if="demographic.type === 'text'"
                :id="'demographic_' + demographic.id"
                type="text" 
                class="form-control"
                :value="getDemographicAnswer(demographic.id)"
                @input="updateDemographicAnswer(demographic.id, $event.target.value)"
                :readonly="readonly"
                :disabled="readonly"
            />
            
            <!-- Select Dropdown -->
            <Select 
                v-else-if="demographic.type === 'select'"
                :id="'demographic_' + demographic.id"
                class="form-select"
                :value="getDemographicAnswer(demographic.id)"
                @change="updateDemographicAnswer(demographic.id, $event.target.value)"
                :readonly="readonly"
                :disabled="readonly"
            >
                <option value="">Please select...</option>
                <option 
                    v-for="option in demographic.options" 
                    :key="option.id"
                    :value="option.value || option.label"
                >
                    {{ option.label }}
                </option>
            </Select>
            
            <!-- Radio Buttons -->
            <div v-else-if="demographic.type === 'radio'" class="mt-1">
                <div v-for="option in demographic.options" :key="option.id" class="form-check">
                    <input 
                        :id="'demographic_' + demographic.id + '_' + option.id"
                        :name="'demographic_' + demographic.id"
                        type="radio" 
                        class="form-check-input"
                        :value="option.value || option.label"
                        :checked="getDemographicAnswer(demographic.id) === (option.value || option.label)"
                        @change="updateDemographicAnswer(demographic.id, $event.target.value)"
                        :readonly="readonly"
                        :disabled="readonly"
                    />
                    <label 
                        :for="'demographic_' + demographic.id + '_' + option.id" 
                        class="form-check-label"
                    >
                        {{ option.label }}
                    </label>
                </div>
            </div>
            
            <!-- Checkboxes -->
            <div v-else-if="demographic.type === 'checkbox'" class="mt-1">
                <div v-for="option in demographic.options" :key="option.id" class="form-check">
                    <input 
                        :id="'demographic_' + demographic.id + '_' + option.id"
                        type="checkbox" 
                        class="form-check-input"
                        :value="option.value || option.label"
                        :checked="isOptionSelected(demographic.id, option.value || option.label)"
                        @change="toggleCheckboxOption(demographic.id, option.value || option.label, $event.target.checked)"
                        :readonly="readonly"
                        :disabled="readonly"
                    />
                    <label 
                        :for="'demographic_' + demographic.id + '_' + option.id" 
                        class="form-check-label"
                    >
                        {{ option.label }}
                    </label>
                </div>
            </div>
            
            <!-- Multi-select -->
            <Select 
                v-else-if="demographic.type === 'multi-select'"
                :id="'demographic_' + demographic.id"
                class="form-select"
                multiple
                :value="getDemographicAnswerArray(demographic.id)"
                @change="updateMultiSelectAnswer(demographic.id, $event)"
                :readonly="readonly"
                :disabled="readonly"
            >
                <option 
                    v-for="option in demographic.options" 
                    :key="option.id"
                    :value="option.value || option.label"
                >
                    {{ option.label }}
                </option>
            </Select>
            
            <small v-if="demographic.description" class="form-text text-muted">
                {{ demographic.description }}
            </small>
        </div>
    </div>
</template>

<script>
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import Label from '@/Components/Label.vue';

export default {
    name: 'StudentDemographics',
    components: {
        Input,
        Select,
        Label
    },
    props: {
        demographics: {
            type: Array,
            default: () => []
        },
        modelValue: {
            type: Object,
            default: () => ({})
        },
        existingDemographics: {
            type: Object,
            default: () => ({})
        },
        readonly: {
            type: Boolean,
            default: false
        }
    },
    emits: ['update:modelValue'],
    data() {
        const initialData = {};
        
        // Transform modelValue if it's in the formatted array format
        if (Array.isArray(this.modelValue)) {
            this.modelValue.forEach(item => {
                if (item.demographic_id && item.answers) {
                    initialData[item.demographic_id] = item.answers.join(',');
                }
            });
        } else if (this.modelValue && typeof this.modelValue === 'object') {
            Object.assign(initialData, this.modelValue);
        }
        
        return {
            demographicAnswers: { ...this.existingDemographics, ...initialData },
            isUpdatingFromParent: false
        }
    },
    watch: {
        modelValue: {
            handler(newVal) {
                this.isUpdatingFromParent = true;
                
                // Transform formatted data back to internal format
                const internalFormat = {};
                
                if (Array.isArray(newVal)) {
                    newVal.forEach(item => {
                        if (item.demographic_id && item.answers) {
                            internalFormat[item.demographic_id] = item.answers.join(',');
                        }
                    });
                } else if (newVal && typeof newVal === 'object') {
                    // Handle the old format for backwards compatibility
                    Object.assign(internalFormat, newVal);
                }
                
                this.demographicAnswers = { ...this.existingDemographics, ...internalFormat };
                
                this.$nextTick(() => {
                    this.isUpdatingFromParent = false;
                });
            },
            deep: true
        },
        demographicAnswers: {
            handler(newVal) {
                // Don't emit if we're updating from parent to prevent recursion
                if (this.isUpdatingFromParent) {
                    return;
                }
                
                // console.log('Demographics answers watcher fired:', newVal);
                // Transform to the format expected by the controller
                const formattedData = Object.keys(newVal).map(demographicId => {
                    const answerValue = newVal[demographicId];
                    let answers = [];
                    
                    if (answerValue) {
                        // Handle comma-separated values (from checkboxes)
                        answers = answerValue.split(',').map(v => v.trim()).filter(v => v !== '');
                        
                        // If it's a single value, make it an array
                        if (answers.length === 0 && answerValue.trim() !== '') {
                            answers = [answerValue.trim()];
                        }
                    }
                    
                    return {
                        demographic_id: parseInt(demographicId),
                        answers: answers
                    };
                }).filter(item => item.answers.length > 0); // Only include demographics with answers
                
                // console.log('Emitting formatted data:', formattedData);
                // console.log('Type of formattedData:', typeof formattedData);
                // console.log('Is array:', Array.isArray(formattedData));
                this.$emit('update:modelValue', formattedData);
            },
            deep: true
        }
    },
    methods: {
        getDemographicAnswer(demographicId) {
            return this.demographicAnswers[demographicId] || '';
        },
        
        getDemographicAnswerArray(demographicId) {
            const answer = this.demographicAnswers[demographicId];
            if (Array.isArray(answer)) {
                return answer;
            }
            return answer ? answer.split(',') : [];
        },
        
        updateDemographicAnswer(demographicId, value) {
            if (this.readonly) return;
            // console.log('Updating demographic answer:', demographicId, value);
            // console.log('Before update:', this.demographicAnswers);
            // console.log('isUpdatingFromParent:', this.isUpdatingFromParent);
            this.demographicAnswers[demographicId] = value;
            // console.log('After update:', this.demographicAnswers);
        },
        
        updateMultiSelectAnswer(demographicId, event) {
            if (this.readonly) return;
            const selectedOptions = Array.from(event.target.selectedOptions, option => option.value);
            // console.log('Updating multi-select answer:', demographicId, selectedOptions);
            this.demographicAnswers[demographicId] = selectedOptions.join(',');
        },
        
        isOptionSelected(demographicId, optionValue) {
            const answer = this.demographicAnswers[demographicId];
            if (!answer) return false;
            
            // Handle comma-separated values for checkboxes
            const selectedValues = answer.split(',').map(v => v.trim());
            return selectedValues.includes(optionValue);
        },
        
        toggleCheckboxOption(demographicId, optionValue, isChecked) {
            if (this.readonly) return;
            
            const currentAnswer = this.demographicAnswers[demographicId] || '';
            let selectedValues = currentAnswer ? currentAnswer.split(',').map(v => v.trim()) : [];
            
            if (isChecked) {
                if (!selectedValues.includes(optionValue)) {
                    selectedValues.push(optionValue);
                }
            } else {
                selectedValues = selectedValues.filter(v => v !== optionValue);
            }
            
            // console.log('Updating checkbox answer:', demographicId, selectedValues);
            this.demographicAnswers[demographicId] = selectedValues.join(',');
        }
    }
}
</script>
